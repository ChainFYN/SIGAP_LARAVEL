<?php

namespace App\Http\Controllers\Warga;

use App\Http\Controllers\Controller;
use App\Models\Desa;
use App\Models\Jalan;
use App\Models\KategoriKerusakan;
use App\Models\Kecamatan;
use App\Models\Laporan;
use App\Models\Pengguna;
use App\Notifications\LaporanKecamatanNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $wargaId = Session::get('warga_id');
        $query = Laporan::with(['jalan.desa.kecamatan', 'kategori', 'status'])
            ->where('id_pengguna', $wargaId);

        if ($request->filled('status')) {
            $query->where('id_status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('deskripsi', 'like', "%{$search}%")
                    ->orWhereHas('jalan', function ($j) use ($search) {
                        $j->where('nama_jalan', 'like', "%{$search}%");
                    });
            });
        }

        $laporan = $query->latest()->get();

        return view('warga.laporan.index', compact('laporan'));
    }

    public function create()
    {
        $kecamatan = Kecamatan::all();
        $desaGrouped = Desa::all()->groupBy('id_kecamatan');
        $jalanGrouped = Jalan::all()->groupBy('id_desa');
        $kategori = KategoriKerusakan::all();

        return view('warga.laporan.create', compact('kecamatan', 'desaGrouped', 'jalanGrouped', 'kategori'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_jalan' => 'required|exists:jalan,id_jalan',
            'id_kategori' => 'required|exists:kategori_kerusakan,id_kategori',
            'tingkat_bahaya' => 'required|in:Rendah,Sedang,Bahaya Tinggi',
            'deskripsi' => 'required|min:10',
            'foto' => 'required|image|max:5120',
            'latitude' => 'nullable',
            'longitude' => 'nullable',
        ]);

        $wargaId = Session::get('warga_id');

        // Cek duplikasi laporan: field isian dan file foto yang sama persis
        $duplicateQuery = Laporan::where('id_pengguna', $wargaId)
            ->where('id_jalan', $request->id_jalan)
            ->where('id_kategori', $request->id_kategori)
            ->where('tingkat_bahaya', $request->tingkat_bahaya)
            ->where('deskripsi', $request->deskripsi);

        if ($request->hasFile('foto')) {
            $uploadedFileHash = md5_file($request->file('foto')->getRealPath());

            $existingLaporan = $duplicateQuery->get();

            foreach ($existingLaporan as $laporan) {
                $existingFilePath = storage_path('app/public/'.$laporan->url_foto);

                if (file_exists($existingFilePath) && md5_file($existingFilePath) === $uploadedFileHash) {
                    return back()
                        ->withInput()
                        ->withErrors(['duplikat' => 'Laporan dengan data dan foto yang sama persis sudah pernah Anda kirim sebelumnya. Silakan ubah isian atau gunakan foto yang berbeda.']);
                }
            }
        }

        // Jika tidak ada duplikat, simpan foto dan buat laporan
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('laporan', 'public');
        }

        $laporan = Laporan::create([
            'id_pengguna' => $wargaId,
            'id_jalan' => $request->id_jalan,
            'id_kategori' => $request->id_kategori,
            'tingkat_bahaya' => $request->tingkat_bahaya,
            'id_status' => 1, // Menunggu Validasi
            'deskripsi' => $request->deskripsi,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'url_foto' => $fotoPath,
        ]);

        // Muat relasi jalan untuk pesan notifikasi yang informatif
        $laporan->load('jalan');

        // Kirim notifikasi laporan baru ke pihak Kecamatan (id_peran = 2)
        $petugasKecamatan = Pengguna::where('id_peran', 2)->get();
        Notification::send($petugasKecamatan, new LaporanKecamatanNotification(
            'laporan_baru',
            'Laporan Baru Masuk',
            "Warga melaporkan kerusakan jalan ({$laporan->tingkat_bahaya}) di " . ($laporan->jalan->nama_jalan ?? 'wilayah desa') . ".",
            $laporan->id_laporan,
            route('kecamatan.validation.index', ['ticket' => $laporan->id_laporan])
        ));

        return redirect()->route('warga.laporan.index')->with('success', 'Laporan berhasil dikirim dan masuk dalam antrean verifikasi!');
    }

    public function show($id)
    {
        $wargaId = Session::get('warga_id');
        $laporan = Laporan::with([
            'jalan.desa.kecamatan',
            'kategori',
            'status',
            'validasi.pengguna',
            'logProses.status',
            'logProses.pengguna',
        ])
            ->where('id_pengguna', $wargaId)
            ->findOrFail($id);

        return view('warga.laporan.show', compact('laporan'));
    }
}
