@extends('pupr.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Riwayat Penanganan Laporan</h1>
            <p class="text-sm text-slate-500 mt-1">Daftar progres pekerjaan fisik lapangan (25% - 100%) dan status verifikasi BAST.</p>
        </div>

        <!-- Filter Tab -->
        <div class="flex items-center gap-2 bg-slate-200/70 p-1 rounded-xl text-xs font-bold">
            <button type="button" onclick="filterRiwayat('semua', this)"
               class="filter-riwayat-btn px-4 py-2 rounded-lg transition-all {{ $filterStatus === 'semua' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                Semua (25% - 100%)
            </button>
            <button type="button" onclick="filterRiwayat('25-75', this)"
               class="filter-riwayat-btn px-4 py-2 rounded-lg transition-all {{ $filterStatus === '25-75' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                Sedang Berjalan (25% - 75%)
            </button>
            <button type="button" onclick="filterRiwayat('100', this)"
               class="filter-riwayat-btn px-4 py-2 rounded-lg transition-all {{ $filterStatus === '100' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                Selesai (100%)
            </button>
        </div>
    </div>

    <!-- Tabel / List Riwayat (Container AJAX) -->
    <div id="riwayat-table-container">
        @include('pupr.riwayat.partials.table')
    </div>
</div>

<script>
function filterRiwayat(status, el) {
    const container = document.getElementById('riwayat-table-container');
    if (!container) return;

    container.style.opacity = '0.5';

    const url = new URL(window.location);
    const search = url.searchParams.get('search') || '';
    let fetchUrl = `{{ route('pupr.riwayat') }}?status=${status}`;
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

        // Update URL tanpa reload / ganti halaman
        if (status === 'semua') {
            url.searchParams.delete('status');
        } else {
            url.searchParams.set('status', status);
        }
        window.history.replaceState({}, '', url);

        // Update class tombol aktif
        document.querySelectorAll('.filter-riwayat-btn').forEach(btn => {
            btn.className = 'filter-riwayat-btn px-4 py-2 rounded-lg transition-all text-slate-600 hover:text-slate-900';
        });
        el.className = 'filter-riwayat-btn px-4 py-2 rounded-lg transition-all bg-white text-slate-900 shadow-sm';
    })
    .catch(error => {
        console.error('Error filter riwayat:', error);
        container.style.opacity = '1';
    });
}
</script>
@endsection
