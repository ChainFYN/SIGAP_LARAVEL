@extends('kecamatan.layouts.app')

@section('title', 'Validasi Laporan - Pemeriksaan & Disposisi Aduan Warga')

@section('content')
<div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <!-- BREADCRUMB & HEADER -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pb-6 border-b border-slate-200">
        <div>
            <nav class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('kecamatan.dashboard') }}" class="hover:text-slate-700 transition-colors">Halaman Utama</a>
                <span>/</span>
                <span class="text-slate-800">Validasi Laporan Masuk</span>
            </nav>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Pemeriksaan & Disposisi Aduan Warga
                </h1>
            </div>
        </div>

        <!-- Filter Waktu (AJAX Tanpa Ganti Halaman) -->
        <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl self-start lg:self-auto overflow-x-auto max-w-full text-xs shrink-0">
            <span class="px-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider hidden sm:inline-flex items-center gap-1">
                <i class="fa-regular fa-calendar text-[11px]"></i> Filter:
            </span>
            <button type="button" onclick="filterWaktuValidation('all', this)"
               class="filter-waktu-btn px-3 py-1.5 rounded-lg font-bold transition-all whitespace-nowrap {{ $timeFilter === 'all' || !$timeFilter ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Semua
            </button>
            <button type="button" onclick="filterWaktuValidation('hari_ini', this)"
               class="filter-waktu-btn px-3 py-1.5 rounded-lg font-bold transition-all whitespace-nowrap {{ $timeFilter === 'hari_ini' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Hari Ini
            </button>
            <button type="button" onclick="filterWaktuValidation('1_minggu', this)"
               class="filter-waktu-btn px-3 py-1.5 rounded-lg font-bold transition-all whitespace-nowrap {{ $timeFilter === '1_minggu' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                1 Minggu Terakhir
            </button>
            <button type="button" onclick="filterWaktuValidation('1_bulan', this)"
               class="filter-waktu-btn px-3 py-1.5 rounded-lg font-bold transition-all whitespace-nowrap {{ $timeFilter === '1_bulan' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Sebulan Terakhir
            </button>
        </div>
    </div>

    <!-- MAIN TWO-COLUMN VALIDATION WORKSPACE (Container AJAX) -->
    <div id="validation-workspace">
        @include('kecamatan.validation.partials.workspace')
    </div>

</div>
@endsection

@push('scripts')
<script>
let currentTicketMap = null;

function initTicketMap() {
    const mapEl = document.getElementById('ticketMap');
    if (!mapEl) return;

    const lat = parseFloat(mapEl.getAttribute('data-lat'));
    const lng = parseFloat(mapEl.getAttribute('data-lng'));
    const ticketId = mapEl.getAttribute('data-id');
    const jalan = mapEl.getAttribute('data-jalan');

    if (isNaN(lat) || isNaN(lng) || typeof L === 'undefined') return;

    if (currentTicketMap) {
        currentTicketMap.remove();
        currentTicketMap = null;
    }

    currentTicketMap = L.map('ticketMap', {
        zoomControl: false,
        attributionControl: false
    }).setView([lat, lng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
    }).addTo(currentTicketMap);

    L.control.zoom({ position: 'bottomright' }).addTo(currentTicketMap);

    const marker = L.circleMarker([lat, lng], {
        radius: 9,
        fillColor: "#ef4444",
        color: "#ffffff",
        weight: 2,
        opacity: 1,
        fillOpacity: 0.95
    }).addTo(currentTicketMap);

    marker.bindPopup(`<strong class='font-sans text-xs'>Laporan #${ticketId}</strong><br><span class='text-[11px]'>${jalan}</span>`).openPopup();
}

document.addEventListener("DOMContentLoaded", function () {
    initTicketMap();
});

function filterWaktuValidation(waktu, el) {
    const container = document.getElementById('validation-workspace');
    if (!container) return;

    container.style.opacity = '0.5';

    const url = new URL(window.location);
    const tab = url.searchParams.get('tab') || 'pending';
    const search = url.searchParams.get('search') || '';

    let fetchUrl = `{{ route('kecamatan.validation.index') }}?waktu=${waktu}&tab=${encodeURIComponent(tab)}`;
    if (search) {
        fetchUrl += `&search=${encodeURIComponent(search)}`;
    }

    fetch(fetchUrl, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        container.innerHTML = html;
        container.style.opacity = '1';

        // Inisialisasi ulang Alpine.js untuk action form
        if (window.Alpine && typeof window.Alpine.initTree === 'function') {
            window.Alpine.initTree(container);
        }

        // Inisialisasi ulang Leaflet Map
        initTicketMap();

        // Update URL tanpa reload halaman
        if (waktu === 'all') {
            url.searchParams.delete('waktu');
        } else {
            url.searchParams.set('waktu', waktu);
        }
        window.history.replaceState({}, '', url);

        // Update class tombol aktif
        document.querySelectorAll('.filter-waktu-btn').forEach(btn => {
            btn.className = 'filter-waktu-btn px-3 py-1.5 rounded-lg font-bold transition-all whitespace-nowrap text-slate-600 hover:text-slate-900';
        });
        el.className = 'filter-waktu-btn px-3 py-1.5 rounded-lg font-bold transition-all whitespace-nowrap bg-white text-slate-900 shadow-xs';
    })
    .catch(error => {
        console.error('Error filter validasi:', error);
        container.style.opacity = '1';
    });
}
</script>
@endpush
