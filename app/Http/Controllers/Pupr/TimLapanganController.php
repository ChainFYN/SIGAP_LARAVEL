<?php

namespace App\Http\Controllers\Pupr;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use App\Models\TimLapangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimLapanganController extends Controller
{
    public function index()
    {
        if (! session()->has('pupr_id')) {
            return redirect()->route('warga.login')->withErrors(['login' => 'Silakan login terlebih dahulu.']);
        }

        // Pastikan 4 tim standar tersedia di database
        if (TimLapangan::count() < 4) {
            $defaultTeams = [
                ['nama_tim' => 'Tim 1', 'jumlah_personel' => 5, 'status' => 'Di Lapangan'],
                ['nama_tim' => 'Tim 2', 'jumlah_personel' => 4, 'status' => 'Di Lapangan'],
                ['nama_tim' => 'Tim 3', 'jumlah_personel' => 6, 'status' => 'Tersedia'],
                ['nama_tim' => 'Tim 4', 'jumlah_personel' => 5, 'status' => 'Tersedia'],
            ];

            foreach ($defaultTeams as $dTeam) {
                TimLapangan::firstOrCreate(
                    ['nama_tim' => $dTeam['nama_tim']],
                    [
                        'jumlah_personel' => $dTeam['jumlah_personel'],
                        'status' => $dTeam['status'],
                    ]
                );
            }
        }

        // Ambil data seluruh tim lapangan beserta relasi laporan
        $timLapangan = TimLapangan::with(['laporan' => function ($q) {
            $q->with(['kategori', 'jalan.desa.kecamatan', 'logProses', 'status', 'pengguna']);
        }])->orderBy('id_tim', 'asc')->get();

        // Cari laporan aktif yang sedang ditangani oleh masing-masing tim
        foreach ($timLapangan as $tim) {
            // Prioritaskan laporan dengan status aktif (1: Menunggu Validasi, 2: Diproses PUPR yang belum selesai/diverifikasi BAST)
            $activeLaporan = $tim->laporan
                ->whereIn('id_status', [1, 2])
                ->where('status_bast', '!=', 'DIVERIFIKASI')
                ->sortByDesc('created_at')
                ->first();

            // Jika tidak ada laporan aktif, pastikan tim berstatus Tersedia (Siaga) dan jangan masukkan laporan baru otomatis
            if (! $activeLaporan && $tim->status === 'Di Lapangan') {
                $tim->update(['status' => 'Tersedia']);
                $tim->status = 'Tersedia';
            }

            if ($activeLaporan) {
                $latestLog = $activeLaporan->logProses->sortByDesc('created_at')->first();
                $persen = 0;

                if ($activeLaporan->id_status == 3 || $activeLaporan->status_bast === 'DIVERIFIKASI' || $activeLaporan->status_bast === 'MENUNGGU_VERIFIKASI') {
                    $persen = 100;
                } elseif ($latestLog && preg_match('/Progres\s*(\d+)%/i', $latestLog->catatan_update, $matches)) {
                    $persen = (int) $matches[1];
                } else {
                    $persen = 0;
                }

                $statusName = $activeLaporan->status->nama_status ?? 'Menunggu Validasi';
                // Jika masih menunggu validasi (id_status == 1), buat progres menjadi 0%
                if ($activeLaporan->id_status == 1) {
                    $persen = 0;
                    $statusName = 'Menunggu Validasi';
                }

                $activeLaporan->progres_persen = $persen;
                $activeLaporan->label_status_penanganan = "{$persen}% - {$statusName}";
                $tim->active_laporan = $activeLaporan;
            } else {
                $tim->active_laporan = null;
            }
        }

        // Hitung statistik sesuai data tampilan
        $totalTim = $timLapangan->count();
        $timPenugasan = $timLapangan->filter(function ($t) {
            return $t->active_laporan !== null || $t->status === 'Di Lapangan';
        })->count();
        $timSiaga = max(0, $totalTim - $timPenugasan);

        // Ambil daftar laporan untuk modal penugasan tim (laporan yang belum memiliki tim penugasan)
        $laporanTersedia = Laporan::with(['jalan.desa.kecamatan', 'kategori'])
            ->whereIn('id_status', [1, 2])
            ->whereNull('id_tim')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('pupr.tim-lapangan.index', compact(
            'timLapangan',
            'totalTim',
            'timPenugasan',
            'timSiaga',
            'laporanTersedia'
        ));
    }

    public function tugaskan(Request $request, $id)
    {
        $request->validate([
            'id_laporan' => 'required|exists:laporan,id_laporan',
        ]);

        $tim = TimLapangan::findOrFail($id);
        $tim->update(['status' => 'Di Lapangan']);

        $laporan = Laporan::findOrFail($request->id_laporan);
        $laporan->update([
            'id_tim' => $tim->id_tim,
            'id_status' => 2, // Diproses PUPR
        ]);

        // Catat di log_proses
        DB::table('log_proses')->insert([
            'id_laporan' => $laporan->id_laporan,
            'id_pengguna' => session('pupr_id'),
            'id_tim' => $tim->id_tim,
            'id_status' => 2,
            'catatan_update' => 'Penugasan '.$tim->nama_tim.' ke ruas jalan.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $kodeTiket = 'LP-'.($laporan->created_at ? $laporan->created_at->format('Y') : date('Y')).'-'.str_pad($laporan->id_laporan, 4, '0', STR_PAD_LEFT);

        return back()->with('success', "{$tim->nama_tim} berhasil ditugaskan untuk penanganan {$kodeTiket}.");
    }

    public function tugaskanLaporan(Request $request, $id)
    {
        $request->validate([
            'id_tim' => 'required|exists:tim_lapangan,id_tim',
        ]);

        $tim = TimLapangan::findOrFail($request->id_tim);

        if ($tim->status !== 'Tersedia') {
            return back()->with('error', "{$tim->nama_tim} saat ini tidak tersedia atau sedang bertugas.");
        }

        $laporan = Laporan::findOrFail($id);

        $tim->update(['status' => 'Di Lapangan']);

        $laporan->update([
            'id_tim' => $tim->id_tim,
            'id_status' => 2, // Diproses PUPR
        ]);

        DB::table('log_proses')->insert([
            'id_laporan' => $laporan->id_laporan,
            'id_pengguna' => session('pupr_id'),
            'id_tim' => $tim->id_tim,
            'id_status' => 2,
            'catatan_update' => 'Penugasan '.$tim->nama_tim.' ke ruas jalan dari Dashboard PUPR.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $kodeTiket = 'LP-'.($laporan->created_at ? $laporan->created_at->format('Y') : date('Y')).'-'.str_pad($laporan->id_laporan, 4, '0', STR_PAD_LEFT);

        return back()->with('success', "{$tim->nama_tim} berhasil ditugaskan untuk penanganan {$kodeTiket}.");
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Tersedia,Di Lapangan,Standby',
        ]);

        $tim = TimLapangan::findOrFail($id);
        $tim->update([
            'status' => $request->status,
        ]);

        if ($request->status === 'Tersedia' || $request->status === 'Standby') {
            Laporan::where('id_tim', $tim->id_tim)
                ->whereIn('id_status', [1, 2])
                ->update(['id_tim' => null]);
        }

        return back()->with('success', 'Status tim berhasil diupdate.');
    }

    public function updatePersonel(Request $request, $id)
    {
        $request->validate([
            'jumlah_personel' => 'required|integer|min:1',
        ]);

        $tim = TimLapangan::findOrFail($id);
        $tim->update([
            'jumlah_personel' => $request->jumlah_personel,
        ]);

        return back()->with('success', 'Jumlah personel berhasil diupdate.');
    }
}
