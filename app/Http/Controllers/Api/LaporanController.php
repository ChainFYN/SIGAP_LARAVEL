<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LaporanResource;
use App\Models\Laporan;
use App\Models\Pengguna;
use App\Notifications\LaporanKecamatanNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class LaporanController extends Controller
{
    /**
     * Mengambil daftar laporan (Milik user yang login)
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id_pengguna;

        $query = Laporan::with(['jalan.desa.kecamatan', 'kategori', 'status'])
            ->where('id_pengguna', $userId);

        // Filter opsional berdasarkan status jika dikirim dari Flutter
        if ($request->filled('status')) {
            $query->where('id_status', $request->status);
        }

        // Urutkan dari yang terbaru dan pasang pagination (default 10 item per halaman)
        $perPage = $request->get('per_page', 10);
        $laporan = $query->latest('id_laporan')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Daftar laporan berhasil dimuat.',
            'data'    => LaporanResource::collection($laporan),
            'pagination' => [
                'current_page' => $laporan->currentPage(),
                'last_page'    => $laporan->lastPage(),
                'per_page'     => $laporan->perPage(),
                'total'        => $laporan->total(),
            ],
        ], 200);
    }

    /**
     * Mengirim laporan baru dari aplikasi Flutter (+ Upload Foto)
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_jalan'       => 'required|exists:jalan,id_jalan',
            'id_kategori'    => 'required|exists:kategori_kerusakan,id_kategori',
            'tingkat_bahaya' => 'required|in:Rendah,Sedang,Bahaya Tinggi',
            'deskripsi'      => 'required|string',
            'latitude'       => 'nullable|numeric',
            'longitude'      => 'nullable|numeric',
            'foto'           => 'required|image|mimes:jpeg,png,jpg|max:5120', // Maks 5MB
        ]);

        // Simpan file foto ke storage/app/public/laporan
        $fotoPath = $request->file('foto')->store('laporan', 'public');

        // Buat data laporan baru di database
        $laporan = Laporan::create([
            'id_pengguna'    => $request->user()->id_pengguna,
            'id_jalan'       => $request->id_jalan,
            'id_kategori'    => $request->id_kategori,
            'tingkat_bahaya' => $request->tingkat_bahaya,
            'id_status'      => 1, // Status awal: Menunggu Validasi
            'deskripsi'      => $request->deskripsi,
            'latitude'       => $request->latitude,
            'longitude'      => $request->longitude,
            'url_foto'       => $fotoPath,
        ]);

        // Muat relasi agar format JSON-nya lengkap
        $laporan->load(['jalan.desa.kecamatan', 'kategori', 'status', 'pengguna']);

        // Kirim notifikasi ke admin Kecamatan (id_peran = 2)
        $petugasKecamatan = Pengguna::where('id_peran', 2)->get();
        Notification::send($petugasKecamatan, new LaporanKecamatanNotification(
            'laporan_baru',
            'Laporan Baru Masuk',
            "Warga melaporkan kerusakan jalan ({$laporan->tingkat_bahaya}) di " . ($laporan->jalan->nama_jalan ?? 'wilayah desa') . ".",
            $laporan->id_laporan,
            route('kecamatan.validation.index', ['ticket' => $laporan->id_laporan])
        ));

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil dikirim dan masuk dalam antrean validasi!',
            'data'    => new LaporanResource($laporan),
        ], 201);
    }

    /**
     * Mengambil detail satu laporan beserta catatan validasi & log progres PUPR
     */
    public function show($id)
    {
        $laporan = Laporan::with([
            'jalan.desa.kecamatan',
            'kategori',
            'status',
            'pengguna',
            'validasi.pengguna',
            'logProses.status',
        ])->find($id);

        if (!$laporan) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail laporan berhasil dimuat.',
            'data'    => new LaporanResource($laporan),
        ], 200);
    }
}
