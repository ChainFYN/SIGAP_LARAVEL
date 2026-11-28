@extends('warga.layouts.app')

@section('title', 'Riwayat Laporan Kerusakan Jalan')

@section('content')
<div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8"
     x-data="{
        status: {{ \Illuminate\Support\Js::from((string) ($initialStatus ?: 'all')) }},
        search: {{ \Illuminate\Support\Js::from((string) ($initialSearch ?? '')) }},
        hasVisibleRows: true,
        init() {
            this.checkVisibility();
            this.$watch('status', (val) => {
                const url = new URL(window.location);
                if (!val || val === 'all') {
                    url.searchParams.delete('status');
                } else {
                    url.searchParams.set('status', val);
                }
                window.history.replaceState({}, '', url);
                this.$nextTick(() => this.checkVisibility());
            });
            this.$watch('search', () => this.$nextTick(() => this.checkVisibility()));
        },
        matches(rowStatus, rowText) {
            const matchesStatus = (this.status === 'all' || this.status === rowStatus.toString());
            const q = this.search.toLowerCase().trim();
            const matchesSearch = !q || (rowText || '').toLowerCase().includes(q);
            return matchesStatus && matchesSearch;
        },
        checkVisibility() {
            const rows = document.querySelectorAll('.laporan-item-row');
            let count = 0;
            rows.forEach(r => {
                if (r.style.display !== 'none') {
                    count++;
                }
            });
            this.hasVisibleRows = (count > 0);
        }
     }">

    <!-- PAGE HEADER -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Riwayat Laporan Anda</h1>
        </div>
    </div>

    <!-- FILTER & SEARCH BAR -->
    <div class="bg-[#FCFAF9] rounded-2xl p-4 border border-slate-200 shadow-xs mb-6">
        <div class="flex flex-col sm:flex-row items-center gap-3">
            <!-- Live Search (Kiri) -->
            <div class="relative flex-1 w-full">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text"
                       x-model="search"
                       placeholder="Cari berdasarkan nama jalan"
                       class="w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-400 bg-slate-50 text-slate-800 placeholder-slate-400">
                <button type="button"
                        x-show="search.length > 0"
                        x-cloak
                        @click="search = ''"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-0.5 text-xs"
                        title="Hapus pencarian">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Status Filter Dropdown (Kanan) -->
            <div class="w-full sm:w-56 shrink-0">
                <select x-model="status"
                        aria-label="Filter Status Laporan"
                        class="w-full py-2 px-3 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-400 bg-slate-50 text-slate-700 cursor-pointer">
                    <option value="all">Semua Status Laporan ({{ $countSemua }})</option>
                    <option value="1">Menunggu Validasi ({{ $countMenunggu }})</option>
                    <option value="2">Diproses PUPR ({{ $countDiproses }})</option>
                    <option value="3">Selesai Diperbaiki ({{ $countSelesai }})</option>
                    <option value="4">Ditolak ({{ $countDitolak }})</option>
                </select>
            </div>
        </div>
    </div>

    <!-- TABLE CARD -->
    <div class="bg-[#F5F5F5] saturate-50 rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/75 text-[11px] font-extrabold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-4 w-12 text-center">No</th>
                        <th class="py-3.5 px-4">ID Tiket / Waktu</th>
                        <th class="py-3.5 px-4">Lokasi Ruas Jalan</th>
                        <th class="py-3.5 px-4">Kategori & Urgensi</th>
                        <th class="py-3.5 px-4">Status Penanganan</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($laporan as $index => $item)
                        <tr class="hover:bg-slate-50/60 transition-colors laporan-item-row"
                            data-status="{{ $item->id_status }}"
                            data-search="{{ strtolower(($item->jalan->nama_jalan ?? '') . ' ' . ($item->jalan->desa->nama_desa ?? '') . ' ' . ($item->jalan->desa->kecamatan->nama_kecamatan ?? '') . ' ' . ($item->kategori->nama_kategori ?? '') . ' ' . ($item->tingkat_bahaya ?? '') . ' LP-' . $item->created_at->format('Y') . '-' . str_pad($item->id_laporan, 4, '0', STR_PAD_LEFT)) }}"
                            x-show="matches($el.dataset.status, $el.dataset.search)">
                            <!-- No -->
                            <td class="py-4 px-4 text-center font-bold text-slate-400">
                                {{ $index + 1 }}
                            </td>

                            <!-- Tiket & Waktu -->
                            <td class="py-4 px-4">
                                <div class="font-mono font-bold text-slate-900 text-xs">
                                    LP-{{ $item->created_at->format('Y') }}-{{ str_pad($item->id_laporan, 4, '0', STR_PAD_LEFT) }}
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    {{ $item->created_at->format('d M Y, H:i') }} WIB
                                </div>
                            </td>

                            <!-- Lokasi Ruas Jalan -->
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-900">
                                    Jl. {{ $item->jalan->nama_jalan ?? '-' }}
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    {{ $item->jalan->desa->nama_desa ?? '-' }}, Kec. {{ $item->jalan->desa->kecamatan->nama_kecamatan ?? '-' }}
                                </div>
                                @if($item->latitude && $item->longitude)
                                    <div class="text-[10px] text-emerald-700 font-medium mt-1">
                                        <i class="fa-solid fa-location-dot text-[9px] mr-1"></i>GPS: {{ number_format($item->latitude, 4) }}, {{ number_format($item->longitude, 4) }}
                                    </div>
                                @endif
                            </td>

                            <!-- Kategori & Urgensi -->
                            <td class="py-4 px-4">
                                <div class="font-semibold text-slate-800">
                                    {{ $item->kategori->nama_kategori ?? 'Umum' }}
                                </div>
                                <div class="mt-1">
                                    <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider {{ $item->tingkat_bahaya === 'Bahaya Tinggi' ? 'bg-red-50 text-red-700 border border-red-200' : ($item->tingkat_bahaya === 'Sedang' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-700 border border-slate-200') }}">
                                        {{ $item->tingkat_bahaya }}
                                    </span>
                                </div>
                            </td>

                            <!-- Status Penanganan -->
                            <td class="py-4 px-4">
                                @php
                                    $badgeBg = 'bg-slate-100 text-slate-700 border-slate-200';
                                    $dotColor = 'bg-slate-400';
                                    if($item->id_status == 1) {
                                        $badgeBg = 'bg-amber-50 text-amber-800 border-amber-200';
                                        $dotColor = 'bg-amber-500';
                                    } elseif($item->id_status == 2) {
                                        $badgeBg = 'bg-blue-50 text-blue-800 border-blue-200';
                                        $dotColor = 'bg-blue-500';
                                    } elseif($item->id_status == 3) {
                                        $badgeBg = 'bg-emerald-50 text-emerald-800 border-emerald-200';
                                        $dotColor = 'bg-emerald-500';
                                    } elseif($item->id_status == 4) {
                                        $badgeBg = 'bg-red-50 text-red-800 border-red-200';
                                        $dotColor = 'bg-red-500';
                                    }
                                @endphp
                                <span class="inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-1 rounded-full border {{ $badgeBg }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }}"></span>
                                    {{ $item->status->nama_status ?? 'Menunggu' }}
                                </span>
                            </td>

                            <!-- Aksi -->
                            <td class="py-4 px-4 text-center">
                                <a href="{{ route('warga.laporan.show', $item->id_laporan) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-lg text-xs transition">
                                    <span>Detail</span>
                                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 px-4 text-center">
                                <div class="w-14 h-14 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    <i class="fa-regular fa-folder-open text-xl"></i>
                                </div>
                                <h4 class="text-sm font-bold text-slate-800 mb-1">Tidak Ada Laporan Ditemukan</h4>
                                <p class="text-xs text-slate-500 max-w-sm mx-auto mb-4">
                                    @if(request('search') || request('status'))
                                        Tidak ditemukan laporan yang sesuai kriteria pencarian Anda.
                                    @else
                                        Anda belum pernah membuat laporan kerusakan jalan.
                                    @endif
                                </p>
                                <a href="{{ route('warga.laporan.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition">
                                    <i class="fa-solid fa-plus"></i>
                                    <span>Buat Laporan Baru</span>
                                </a>
                            </td>
                        </tr>
                    @endforelse

                    @if($laporan->isNotEmpty())
                        <tr x-show="!hasVisibleRows" x-cloak>
                            <td colspan="6" class="py-12 px-4 text-center">
                                <div class="w-14 h-14 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    <i class="fa-regular fa-folder-open text-xl"></i>
                                </div>
                                <h4 class="text-sm font-bold text-slate-800 mb-1">Tidak Ada Laporan yang Sesuai</h4>
                                <p class="text-xs text-slate-500 max-w-sm mx-auto mb-4">
                                    Tidak ditemukan laporan dengan filter status atau kata kunci pencarian yang dipilih.
                                </p>
                                <button type="button" @click="status = 'all'; search = '';" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl text-xs font-bold transition">
                                    <i class="fa-solid fa-rotate-left text-[11px]"></i>
                                    <span>Tampilkan Semua Laporan</span>
                                </button>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
