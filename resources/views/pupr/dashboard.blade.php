@extends('pupr.layouts.app')

@section('title', 'Dashboard PUPR')

@section('content')
<div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8">

<!-- WELCOME BANNER -->
<div class="bg-slate-900 rounded-2xl p-6 sm:p-8 text-white mb-6 shadow-sm relative overflow-hidden">
    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                Selamat Datang, {{ session('pupr_name', 'Admin PUPR') }}!
            </h1>
            <p class="text-slate-300 text-xs sm:text-sm mt-1 max-w-2xl leading-relaxed">
                Tindak lanjuti laporan yang sudah divalidasi kecamatan, tugaskan tim lapangan, dan perbarui progres perbaikan jalan secara transparan.
            </p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('pupr.laporan.index') }}"
               class="px-5 py-3 rounded-xl bg-[#F5F5F5] saturate-50 hover:bg-[#f2ddd1] text-slate-900 font-bold text-xs sm:text-sm transition flex items-center gap-2 shadow-lg shadow-black/10">
                <i class="fa-solid fa-list-check"></i>
                <span>Lihat Laporan Masuk</span>
            </a>
        </div>
    </div>
</div>

    <!-- TOP HEADER & TIME FILTER -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Dashboard PUPR</h1>
        </div>

        <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl self-start md:self-auto overflow-x-auto max-w-full text-xs">
            <span class="px-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider hidden sm:inline-flex items-center gap-1">
                <i class="fa-regular fa-calendar text-[11px]"></i> Filter:
            </span>
            <button type="button" onclick="filterPupr('all', this)"
               class="filter-pupr-btn px-3 py-1.5 rounded-lg font-bold transition-all whitespace-nowrap {{ $timeFilter === 'all' || !$timeFilter ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Semua
            </button>
            <button type="button" onclick="filterPupr('hari_ini', this)"
               class="filter-pupr-btn px-3 py-1.5 rounded-lg font-bold transition-all whitespace-nowrap {{ $timeFilter === 'hari_ini' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Hari Ini
            </button>
            <button type="button" onclick="filterPupr('1_minggu', this)"
               class="filter-pupr-btn px-3 py-1.5 rounded-lg font-bold transition-all whitespace-nowrap {{ $timeFilter === '1_minggu' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                1 Minggu Terakhir
            </button>
            <button type="button" onclick="filterPupr('1_bulan', this)"
               class="filter-pupr-btn px-3 py-1.5 rounded-lg font-bold transition-all whitespace-nowrap {{ $timeFilter === '1_bulan' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Sebulan Terakhir
            </button>
        </div>
    </div>

    <!-- DASHBOARD CONTENT (UPDATED VIA AJAX WITHOUT RELOAD) -->
    <div id="pupr-dashboard-content">
        @include('pupr.partials.dashboard_content')
    </div>

</div>

<script>
function filterPupr(waktu, el) {
    const container = document.getElementById('pupr-dashboard-content');
    if (!container) return;

    container.style.opacity = '0.5';

    fetch(`{{ route('pupr.dashboard') }}?waktu=${waktu}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        container.innerHTML = html;
        container.style.opacity = '1';

        // Inisialisasi ulang Alpine.js untuk interaktivitas filter tab
        if (window.Alpine && typeof window.Alpine.initTree === 'function') {
            window.Alpine.initTree(container);
        }

        // Update URL tanpa reload halaman (Back browser tetap normal)
        const url = new URL(window.location);
        if (waktu === 'all') {
            url.searchParams.delete('waktu');
        } else {
            url.searchParams.set('waktu', waktu);
        }
        window.history.replaceState({}, '', url);

        // Perbarui class tombol aktif
        document.querySelectorAll('.filter-pupr-btn').forEach(btn => {
            btn.className = 'filter-pupr-btn px-3 py-1.5 rounded-lg font-bold transition-all whitespace-nowrap text-slate-600 hover:text-slate-900';
        });
        el.className = 'filter-pupr-btn px-3 py-1.5 rounded-lg font-bold transition-all whitespace-nowrap bg-white text-slate-900 shadow-xs';
    })
    .catch(error => {
        console.error('Error filter antrean PUPR:', error);
        container.style.opacity = '1';
    });
}
</script>
@endsection