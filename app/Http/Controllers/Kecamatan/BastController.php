<?php

namespace App\Http\Controllers\Kecamatan;

use App\Http\Controllers\Controller;
use App\Models\Laporan;
use App\Models\LogProses;
use App\Models\Pengguna;
use App\Models\TimLapangan;
use App\Notifications\LaporanPUPRNotification;
use App\Notifications\LaporanWargaNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;

class BastController extends Controller
{
    public function index(Request $request)
    {
        $timeFilter = $request->query('waktu', 'all');
        $tab = $request->query('tab', 'antrean');
        $tab = $tab === 'selesai' ? 'selesai' : 'antrean';
        $search = $request->query('search', '');
        $selectedId = $request->query('ticket');

        $applyTimeFilter = function ($query) use ($timeFilter) {
            return match ($timeFilter) {
                'hari_ini', 'today' => $query->where(function ($q) {
                    $q->whereDate('updated_at', now()->today())
                        ->orWhereDate('created_at', now()->today());
                }),
                'kemarin', 'yesterday' => $query->where(function ($q) {
                    $q->whereDate('updated_at', now()->subDay()->toDateString())
                        ->orWhereDate('created_at', now()->subDay()->toDateString());
                }),
                '1_minggu', 'minggu_ini', 'week' => $query->where(function ($q) {
                    $q->where('updated_at', '>=', now()->subDays(7)->startOfDay())
                        ->orWhere('created_at', '>=', now()->subDays(7)->startOfDay());
                }),
                '1_bulan', 'bulan_ini', 'month' => $query->where(function ($q) {
                    $q->where('updated_at', '>=', now()->subDays(30)->startOfDay())
                        ->orWhere('created_at', '>=', now()->subDays(30)->startOfDay());
                }),
                default => $query,
            };
        };

        $antreanScope = function ($query) {
            return $query->where(function ($q) {
                $q->where('status_bast', 'MENUNGGU_VERIFIKASI')
                    ->orWhere(function ($q2) {
                        $q2->where('id_status', 3)->whereNull('status_bast');
                    });
            });
        };

        $selesaiScope = function ($query) {
            return $query->where('id_status', 3)
                ->where('status_bast', 'DIVERIFIKASI');
        };

        // Hitung jumlah tab sesuai filter waktu aktif
        $antreanCount = $applyTimeFilter($antreanScope(Laporan::query()))->count();
        $selesaiCount = $applyTimeFilter($selesaiScope(Laporan::query()))->count();

        // Query daftar laporan berdasarkan tab aktif
        $query = Laporan::with(['jalan.desa.kecamatan', 'kategori', 'pengguna', 'logProses']);
        $query = $tab === 'selesai' ? $selesaiScope($query) : $antreanScope($query);
        $query = $applyTimeFilter($query);

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('id_laporan', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%")
                    ->orWhereHas('pengguna', fn ($q2) => $q2->where('nama_lengkap', 'like', "%{$search}%"))
                    ->orWhereHas('jalan', fn ($q2) => $q2->where('nama_jalan', 'like', "%{$search}%"));
            });
        }

        $laporan_bast = $query->orderBy('updated_at', 'desc')->get();

        $activeBast = null;
        if ($selectedId) {
            $activeBast = $laporan_bast->firstWhere('id_laporan', (int) $selectedId);
        }
        if (! $activeBast && $laporan_bast->isNotEmpty()) {
            $activeBast = $laporan_bast->first();
        }

        $filterLabel = match ($timeFilter) {
            'hari_ini', 'today' => 'Hari Ini',
            'kemarin', 'yesterday' => 'Kemarin',
            '1_minggu', 'minggu_ini', 'week' => '1 Minggu Terakhir',
            '1_bulan', 'bulan_ini', 'month' => 'Sebulan Terakhir',
            default => 'Semua Waktu',
        };

        $viewData = compact(
            'laporan_bast',
            'activeBast',
            'tab',
            'antreanCount',
            'selesaiCount',
            'timeFilter',
            'filterLabel',
            'search'
        );

        if ($request->ajax()) {
            return view('kecamatan.bast.partials.workspace', $viewData);
        }

        return view('kecamatan.bast.index', $viewData);
    }

    public function update(Request $request, $id)
    {
        $laporan = Laporan::with(['jalan', 'pengguna'])->findOrFail($id);
        $action = $request->input('action', 'acc');
        $catatan = trim((string) $request->input('catatan', ''));
        $kecamatanId = Session::get('kecamatan_id') ?? 1;

        if ($action === 'acc') {
            // Camat verifikasi Laporan PUPR dan kirim ke warga
            $laporan->update([
                'id_status' => 3, // Selesai resmi
                'status_bast' => 'DIVERIFIKASI',
                'catatan_bast' => $catatan ?: 'Telah dilakukan uji petik lapangan dan disetujui Laporan PUPR.',
                'tgl_verifikasi_bast' => now(),
            ]);

            // Kembalikan status tim lapangan menjadi Tersedia (Siaga)
            if ($laporan->id_tim) {
                TimLapangan::where('id_tim', $laporan->id_tim)->update(['status' => 'Tersedia']);
            }

            LogProses::create([
                'id_laporan' => $laporan->id_laporan,
                'id_pengguna' => $kecamatanId,
                'id_status' => 3,
                'catatan_update' => 'Laporan PUPR diverifikasi Camat & diterbitkan ke warga. Catatan: '.($catatan ?: 'Lolos uji petik & spesifikasi teknis.'),
            ]);

            // 1. Notifikasi ke Petugas PUPR bahwa Laporan PUPR disetujui resmi
            $petugasPupr = Pengguna::where('id_peran', 3)->get();
            Notification::send($petugasPupr, new LaporanPUPRNotification(
                'bast_disetujui',
                'Laporan PUPR Diverifikasi Camat (Selesai)',
                "Laporan PUPR #LP-{$laporan->id_laporan} di ".($laporan->jalan->nama_jalan ?? 'jalan desa').' telah diverifikasi & disetujui oleh Camat.',
                $laporan->id_laporan,
                route('pupr.riwayat', ['status' => '100'])
            ));

            // 2. Notifikasi ke Warga Pelapor bahwa jalan selesai diperbaiki
            if ($laporan->pengguna) {
                $laporan->pengguna->notify(new LaporanWargaNotification(
                    'laporan_selesai',
                    'Jalan Selesai Diperbaiki & Laporan PUPR Sah',
                    'Kabar baik! Perbaikan jalan di '.($laporan->jalan->nama_jalan ?? 'lokasi laporan Anda').' telah selesai 100% dan disahkan melalui Laporan PUPR.',
                    $laporan->id_laporan,
                    route('warga.laporan.show', $laporan->id_laporan)
                ));
            }

            return redirect()->route('kecamatan.bast.index')
                ->with('status', "Laporan PUPR #{$laporan->id_laporan} berhasil diverifikasi & dikirim ke warga!")
                ->with('status_type', 'success');
        } else {
            // Tolak / Minta Perbaikan Lapangan ke PUPR
            $laporan->update([
                'id_status' => 2, // Kembali Diproses PUPR
                'status_bast' => 'DITOLAK',
                'catatan_bast' => $catatan ?: 'Diminta perbaikan minor oleh Kecamatan.',
                'tgl_verifikasi_bast' => now(),
            ]);

            // KIRIM NOTIFIKASI KE PETUGAS PUPR
            $petugasPupr = Pengguna::where('id_peran', 3)->get();
            Notification::send($petugasPupr, new LaporanPUPRNotification(
                'bast_ditolak',
                'Laporan PUPR Ditolak (Perlu Perbaikan)',
                "Laporan #LP-{$laporan->id_laporan} perlu perbaikan: ".($catatan ?: 'Perbaikan minor belum sesuai.'),
                $laporan->id_laporan,
                route('pupr.progres.show', $laporan->id_laporan)
            ));

            LogProses::create([
                'id_laporan' => $laporan->id_laporan,
                'id_pengguna' => $kecamatanId,
                'id_status' => 2,
                'catatan_update' => 'Laporan PUPR ditolak oleh Camat. Diminta perbaikan lapangan: '.($catatan ?: 'Perbaikan minor belum memenuhi toleransi.'),
            ]);

            return redirect()->route('kecamatan.bast.index')
                ->with('status', "Laporan PUPR #{$laporan->id_laporan} ditolak dan dikembalikan ke Dinas PUPR untuk perbaikan.")
                ->with('status_type', 'warning');
        }
    }
}
