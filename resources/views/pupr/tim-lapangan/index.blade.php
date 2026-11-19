@extends('pupr.layouts.app')

@section('title', 'Status Penugasan & Tim Lapangan - PUPR')

@section('content')
<div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- PAGE HEADER & STAT CARDS -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl sm:text-[26px] font-black text-slate-900 tracking-tight">Status Penugasan & Tim Lapangan</h1>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <!-- TOTAL TIM -->
            <div class="bg-white rounded-2xl border border-slate-200/90 px-4 py-2 flex items-center gap-3.5 shadow-2xs min-w-[130px]">
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm shrink-0">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider leading-none mb-1">TOTAL TIM</div>
                    <div class="text-sm font-black text-slate-900 leading-none">{{ $totalTim ?? 4 }} Tim</div>
                </div>
            </div>

            <!-- PENUGASAN -->
            <div class="bg-[#fffbeb] border border-amber-200/90 rounded-2xl px-4 py-2 flex items-center gap-3.5 shadow-2xs min-w-[130px]">
                <div class="w-9 h-9 rounded-xl bg-amber-100/80 text-amber-700 flex items-center justify-center text-sm shrink-0">
                    <i class="fa-regular fa-pen-to-square"></i>
                </div>
                <div>
                    <div class="text-[10px] font-bold text-amber-800 uppercase tracking-wider leading-none mb-1">PENUGASAN</div>
                    <div class="text-sm font-black text-amber-950 leading-none">{{ $timPenugasan ?? 2 }} Tim</div>
                </div>
            </div>

            <!-- SIAGA -->
            <div class="bg-[#ecfdf5] border border-emerald-200/90 rounded-2xl px-4 py-2 flex items-center gap-3.5 shadow-2xs min-w-[130px]">
                <div class="w-9 h-9 rounded-xl bg-emerald-100/80 text-emerald-700 flex items-center justify-center text-sm shrink-0">
                    <i class="fa-solid fa-check"></i>
                </div>
                <div>
                    <div class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider leading-none mb-1">SIAGA</div>
                    <div class="text-sm font-black text-emerald-950 leading-none">{{ $timSiaga ?? 2 }} Tim</div>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN CARD & TABLE -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-extrabold uppercase tracking-wider text-slate-400">
                        <th class="py-5 px-6 text-center w-14">NO</th>
                        <th class="py-5 px-6">ID TIKET / WAKTU</th>
                        <th class="py-5 px-6">LOKASI RUAS JALAN</th>
                        <th class="py-5 px-6">KATEGORI & URGENSI</th>
                        <th class="py-5 px-6 text-center">STATUS PENANGANAN</th>
                        <th class="py-5 px-6 text-center">TIM LAPANGAN</th>
                        <th class="py-5 px-6 text-center">DETAIL</th>
                        <th class="py-5 px-6 text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/90 text-xs">
                    @forelse($timLapangan as $index => $tim)
                        @php
                            $laporan = $tim->active_laporan;
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <!-- NO -->
                            <td class="py-5 px-6 text-center font-semibold text-slate-600">
                                {{ $index + 1 }}
                            </td>

                            @if($laporan)
                                <!-- ID TIKET / WAKTU -->
                                <td class="py-5 px-6">
                                    <div class="font-extrabold text-slate-900 text-xs">
                                        LP-{{ $laporan->created_at ? $laporan->created_at->format('Y') : date('Y') }}-{{ str_pad($laporan->id_laporan, 4, '0', STR_PAD_LEFT) }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">
                                        {{ $laporan->created_at ? $laporan->created_at->format('d M Y, H:i') : '-' }} WIB
                                    </div>
                                </td>

                                <!-- LOKASI RUAS JALAN -->
                                <td class="py-5 px-6">
                                    <div class="font-extrabold text-slate-900 text-xs">
                                        {{ str_starts_with($laporan->jalan->nama_jalan ?? '', 'Jl') ? $laporan->jalan->nama_jalan : 'Jl. ' . ($laporan->jalan->nama_jalan ?? '-') }}
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        {{ $laporan->jalan->desa->nama_desa ?? '-' }}, Kec. {{ $laporan->jalan->desa->kecamatan->nama_kecamatan ?? '-' }}
                                    </div>
                                    @if($laporan->latitude && $laporan->longitude)
                                        <div class="text-[10px] text-teal-600 font-semibold mt-1">
                                            GPS: {{ number_format($laporan->latitude, 4) }}, {{ number_format($laporan->longitude, 4) }}
                                        </div>
                                    @endif
                                </td>

                                <!-- KATEGORI & URGENSI -->
                                <td class="py-5 px-6">
                                    <div class="font-extrabold text-slate-900 text-xs">
                                        {{ $laporan->kategori->nama_kategori ?? 'Umum' }}
                                    </div>
                                    <div class="mt-1">
                                        @php
                                            $urgency = strtolower($laporan->tingkat_bahaya ?? '');
                                            if (in_array($urgency, ['tinggi', 'berat', 'bahaya tinggi'])) {
                                                $badgeStyle = 'bg-rose-50 text-rose-600 border-rose-200';
                                            } elseif ($urgency === 'sedang') {
                                                $badgeStyle = 'bg-amber-50 text-amber-700 border-amber-200';
                                            } else {
                                                $badgeStyle = 'bg-slate-100 text-slate-600 border-slate-200';
                                            }
                                        @endphp
                                        <span class="inline-block text-[9px] font-black px-2 py-0.5 rounded tracking-wider uppercase border {{ $badgeStyle }}">
                                            {{ $laporan->tingkat_bahaya ?? 'RENDAH' }}
                                        </span>
                                    </div>
                                </td>

                                <!-- STATUS PENANGANAN -->
                                <td class="py-5 px-6 text-center">
                                    @php
                                        $persenPenanganan = $laporan->progres_persen ?? 0;
                                        if ($persenPenanganan >= 100) {
                                            $badgeCls = 'border-emerald-300 bg-[#f0fdf4] text-emerald-800';
                                            $dotCls = 'bg-emerald-500';
                                        } elseif ($persenPenanganan > 0) {
                                            $badgeCls = 'border-amber-300 bg-[#fffdf0] text-amber-800';
                                            $dotCls = 'bg-amber-500';
                                        } else {
                                            $badgeCls = 'border-blue-200 bg-blue-50 text-blue-700';
                                            $dotCls = 'bg-blue-500';
                                        }
                                    @endphp
                                    <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border {{ $badgeCls }} text-xs font-semibold shadow-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $dotCls }}"></span>
                                        {{ $laporan->label_status_penanganan ?? '0% - Menunggu Validasi' }}
                                    </span>
                                </td>

                                <!-- TIM LAPANGAN -->
                                <td class="py-5 px-6 text-center">
                                    <span class="inline-block px-3.5 py-1 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-700 shadow-2xs">
                                        {{ $tim->nama_tim }}
                                    </span>
                                </td>

                                <!-- DETAIL -->
                                <td class="py-5 px-6 text-center">
                                    <button type="button"
                                            onclick="document.getElementById('modal-detail-{{ $laporan->id_laporan }}').classList.remove('hidden')"
                                            class="text-xs font-extrabold text-slate-800 hover:text-blue-600 transition inline-flex items-center gap-1 cursor-pointer">
                                        Detail &rarr;
                                    </button>
                                </td>

                                <!-- AKSI -->
                                <td class="py-5 px-6 text-center">
                                    <a href="{{ route('pupr.progres.show', $laporan->id_laporan) }}"
                                       class="inline-flex items-center justify-center px-4 py-2.5 bg-[#0f1b33] hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition shadow-xs whitespace-nowrap">
                                        Update Progres Lapangan
                                    </a>
                                </td>
                            @else
                                <!-- KETIKA STATUS SIAGA / BELUM ADA LAPORAN -->
                                <td class="py-5 px-6 text-slate-400 font-bold">-</td>
                                <td class="py-5 px-6 text-slate-400 font-bold">-</td>
                                <td class="py-5 px-6 text-slate-400 font-bold">-</td>

                                <!-- STATUS PENANGANAN SIAGA -->
                                <td class="py-5 px-6 text-center">
                                    <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-emerald-300 bg-[#f0fdf4] text-emerald-800 text-xs font-semibold shadow-2xs">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Siaga / Tersedia
                                    </span>
                                </td>

                                <!-- TIM LAPANGAN -->
                                <td class="py-5 px-6 text-center">
                                    <span class="inline-block px-3.5 py-1 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-700 shadow-2xs">
                                        {{ $tim->nama_tim }}
                                    </span>
                                </td>

                                <!-- DETAIL -->
                                <td class="py-5 px-6 text-center text-slate-400 font-bold">-</td>

                                <!-- AKSI TUGASKAN TIM -->
                                <td class="py-5 px-6 text-center">
                                    <button type="button"
                                            onclick="document.getElementById('modal-tugaskan-{{ $tim->id_tim }}').classList.remove('hidden')"
                                            class="inline-flex items-center justify-center px-5 py-2.5 bg-[#a04a14] hover:bg-[#883d0f] text-white rounded-xl text-xs font-bold transition shadow-xs whitespace-nowrap cursor-pointer">
                                        Tugaskan {{ $tim->nama_tim }}
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 px-4 text-center text-slate-400 font-medium">
                                Belum ada tim lapangan yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- MODAL DETAIL & MODAL PENUGASAN -->
@foreach($timLapangan as $tim)
    @php $laporan = $tim->active_laporan; @endphp

    @if($laporan)
    <!-- MODAL DETAIL LAPORAN -->
    <div id="modal-detail-{{ $laporan->id_laporan }}" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl w-full max-w-3xl max-h-[90vh] overflow-hidden flex flex-col shadow-2xl">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50/70">
                <div>
                    <h3 class="font-extrabold text-slate-900 text-lg">
                        Detail Laporan LP-{{ $laporan->created_at ? $laporan->created_at->format('Y') : date('Y') }}-{{ str_pad($laporan->id_laporan, 4, '0', STR_PAD_LEFT) }}
                    </h3>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">
                        Ditugaskan ke <strong>{{ $tim->nama_tim }}</strong> • Dilaporkan pada {{ $laporan->created_at ? $laporan->created_at->format('d M Y, H:i') : '-' }} WIB
                    </p>
                </div>
                <button type="button" onclick="document.getElementById('modal-detail-{{ $laporan->id_laporan }}').classList.add('hidden')" class="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="p-6 overflow-y-auto space-y-5 text-xs text-slate-700">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Foto -->
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">FOTO KERUSAKAN JALAN</div>
                        <div class="w-full h-52 rounded-2xl overflow-hidden bg-slate-100 border border-slate-200 flex items-center justify-center">
                            @if($laporan->url_foto)
                                <img src="{{ asset('storage/' . $laporan->url_foto) }}" alt="Foto Rusak" class="w-full h-full object-cover">
                            @else
                                <div class="text-center text-slate-400">
                                    <i class="fa-solid fa-image text-3xl mb-1"></i>
                                    <div>Foto tidak tersedia</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Info Laporan -->
                    <div class="space-y-3">
                        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200/80 space-y-2.5">
                            <div>
                                <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block">Lokasi Ruas Jalan</span>
                                <span class="font-bold text-slate-900 text-sm">
                                    {{ str_starts_with($laporan->jalan->nama_jalan ?? '', 'Jl') ? $laporan->jalan->nama_jalan : 'Jl. ' . ($laporan->jalan->nama_jalan ?? '-') }}
                                </span>
                                <div class="text-[11px] text-slate-500">
                                    {{ $laporan->jalan->desa->nama_desa ?? '-' }}, Kec. {{ $laporan->jalan->desa->kecamatan->nama_kecamatan ?? '-' }}
                                </div>
                                @if($laporan->latitude && $laporan->longitude)
                                    <div class="text-[10px] text-teal-600 font-semibold mt-0.5">
                                        GPS: {{ number_format($laporan->latitude, 4) }}, {{ number_format($laporan->longitude, 4) }}
                                    </div>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-200/60">
                                <div>
                                    <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block">Kategori</span>
                                    <span class="font-extrabold text-slate-800">{{ $laporan->kategori->nama_kategori ?? '-' }}</span>
                                </div>
                                <div>
                                    <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block">Urgensi</span>
                                    <span class="font-extrabold text-rose-600">{{ $laporan->tingkat_bahaya ?? '-' }}</span>
                                </div>
                            </div>

                            <div class="pt-2 border-t border-slate-200/60">
                                <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block">Pelapor</span>
                                <span class="font-semibold text-slate-800">{{ $laporan->pengguna->nama_lengkap ?? 'Warga' }}</span>
                            </div>
                        </div>

                        <div>
                            <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider block mb-1">Deskripsi Kerusakan</span>
                            <p class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-slate-700 leading-relaxed text-xs">
                                {{ $laporan->deskripsi ?? 'Tidak ada keterangan tambahan.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/70 flex justify-between items-center">
                <button type="button" onclick="document.getElementById('modal-detail-{{ $laporan->id_laporan }}').classList.add('hidden')" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-xl">
                    Tutup
                </button>
                <a href="{{ route('pupr.progres.show', $laporan->id_laporan) }}" class="px-4 py-2 bg-[#0f1b33] hover:bg-slate-900 text-white text-xs font-bold rounded-xl transition shadow-xs flex items-center gap-1.5">
                    <i class="fa-solid fa-pen-to-square text-[10px]"></i> Update Progres Lapangan
                </a>
            </div>
        </div>
    </div>
    @else
    <!-- MODAL PENUGASAN TIM -->
    <div id="modal-tugaskan-{{ $tim->id_tim }}" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl w-full max-w-lg overflow-hidden flex flex-col shadow-2xl">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center bg-slate-50/70">
                <div>
                    <h3 class="font-extrabold text-slate-900 text-base">
                        Penugasan: {{ $tim->nama_tim }}
                    </h3>
                    <p class="text-[11px] text-slate-500 font-medium mt-0.5">
                        Pilih laporan perbaikan jalan yang akan ditangani oleh {{ $tim->nama_tim }}.
                    </p>
                </div>
                <button type="button" onclick="document.getElementById('modal-tugaskan-{{ $tim->id_tim }}').classList.add('hidden')" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Form -->
            <form action="{{ route('pupr.tim-lapangan.tugaskan', $tim->id_tim) }}" method="POST">
                @csrf
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-2">Pilih Tiket / Lokasi Laporan</label>
                        @if(isset($laporanTersedia) && $laporanTersedia->count() > 0)
                            <select name="id_laporan" required class="w-full text-xs font-medium rounded-xl border border-slate-200 px-3.5 py-2.5 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-400">
                                <option value="">-- Pilih Laporan Jalan --</option>
                                @foreach($laporanTersedia as $lap)
                                    <option value="{{ $lap->id_laporan }}">
                                        LP-{{ $lap->created_at ? $lap->created_at->format('Y') : date('Y') }}-{{ str_pad($lap->id_laporan, 4, '0', STR_PAD_LEFT) }} — {{ $lap->jalan->nama_jalan ?? '-' }} ({{ $lap->kategori->nama_kategori ?? 'Umum' }}) [{{ $lap->tingkat_bahaya ?? 'Normal' }}]
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-slate-400 mt-1.5">
                                Menampilkan laporan yang siap dikerjakan atau belum memiliki tim penugasan.
                            </p>
                        @else
                            <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-xs">
                                <i class="fa-solid fa-triangle-exclamation mr-1 text-amber-600"></i>
                                Belum ada laporan jalan baru yang menunggu penugasan.
                            </div>
                        @endif
                    </div>

                    <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-200 text-[11px] text-slate-600 space-y-1">
                        <div class="font-bold text-slate-800">Informasi Personel:</div>
                        <div>Jumlah Anggota: <strong>{{ $tim->jumlah_personel }} orang</strong></div>
                        <div>Status Saat Ini: <span class="text-emerald-700 font-bold">Siaga / Tersedia</span></div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/70 flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('modal-tugaskan-{{ $tim->id_tim }}').classList.add('hidden')" class="px-4 py-2 text-xs font-bold text-slate-600 hover:text-slate-900 bg-white border border-slate-200 rounded-xl">
                        Batal
                    </button>
                    <button type="submit" @if(!isset($laporanTersedia) || $laporanTersedia->count() == 0) disabled @endif class="px-5 py-2 bg-[#a04a14] hover:bg-[#883d0f] disabled:opacity-50 text-white text-xs font-bold rounded-xl transition shadow-xs">
                        Konfirmasi Penugasan
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
@endforeach

@endsection
