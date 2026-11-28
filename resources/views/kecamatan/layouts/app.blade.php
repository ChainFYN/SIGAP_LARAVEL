<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Admin Kecamatan') - SIGAP Kab. Jember</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c',
                            800: '#9a3412',
                            900: '#7c2d12',
                        },
                        navy: {
                            800: '#0f172a',
                            900: '#0b1329',
                            950: '#050a18',
                        },
                        sigap: {
                            teal: '#0d9488',
                            darkteal: '#134e4a',
                        }
                    }
                }
            }
        }
    </script>

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Leaflet GIS CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .navy-gradient {
            background: linear-gradient(180deg, #0C146A 0%, #101B93 95%, #1523BC 100%);
            box-shadow: 0 4px 18px rgba(20, 30, 113, 0.18);
        }

        .footer-gradient {
            background: linear-gradient(180deg, #1523BC 0%, #101B93 20%, #0C146A 100%);
            box-shadow: 0 -4px 18px rgba(20, 30, 113, 0.12);
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
            }
        }
    </style>
    @stack('styles')
</head>

<body class="h-full flex flex-col text-slate-800 antialiased" x-data="{ searchModalOpen: false }">

    <!-- TOP NAVBAR -->
    <header class="navy-gradient border-b border-white/10 sticky top-0 z-40 shadow-[0_8px_24px_rgba(20,30,113,0.12)] no-print">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">

                <!-- Left: Logo & Portal Title -->
                <div class="flex items-center gap-8">
                    <a href="{{ route('kecamatan.dashboard') }}" class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-white">
                            <i class="fa-solid fa-building text-lg text-emerald-400"></i>
                        </div>
                        <div>
                            <div class="font-extrabold text-xl tracking-tight text-white leading-none">SIGAP</div>
                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-300 mt-1">KECAMATAN</div>
                        </div>
                    </a>

                    <!-- Main Navigation Menu -->
                    <nav class="hidden md:flex items-center gap-1.5 ml-4">
                        <a href="{{ route('kecamatan.dashboard') }}"
                            class="px-3.5 py-2 text-sm font-semibold rounded-lg transition-all {{ request()->routeIs('kecamatan.dashboard') ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-200 hover:text-white hover:bg-white/10' }}">
                            Dashboard Kecamatan
                        </a>
                        <a href="{{ route('kecamatan.validation.index') }}"
                            class="px-3.5 py-2 text-sm font-semibold rounded-lg transition-all relative flex items-center gap-2 {{ request()->routeIs('kecamatan.validation.*') ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-200 hover:text-white hover:bg-white/10' }}">
                            <span>Validasi Laporan</span>
                        </a>
                        @php
                            $bastBadgeCount = \App\Models\Laporan::where('status_bast', 'MENUNGGU_VERIFIKASI')->count();
                        @endphp
                        <a href="{{ route('kecamatan.bast.index') }}"
                            class="px-3.5 py-2 text-sm font-semibold rounded-lg transition-all relative flex items-center gap-2 {{ request()->routeIs('kecamatan.bast.*') ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-200 hover:text-white hover:bg-white/10' }}">
                            <span>Verifikasi Laporan PUPR</span>
                            @if($bastBadgeCount > 0)
                                <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold leading-none text-slate-900 bg-amber-400 rounded">{{ $bastBadgeCount }} Baru</span>
                            @endif
                        </a>
                    </nav>
                </div>

                <!-- Right: Notifications & Profile -->
                <div class="flex items-center gap-5">

                    @php
                        $currentKecamatan = \App\Models\Pengguna::find(session('kecamatan_id'));
                        $allKecNotifs = $currentKecamatan ? $currentKecamatan->notifications()->take(10)->get() : collect();
                        $unreadKecCount = $currentKecamatan ? $currentKecamatan->unreadNotifications()->count() : 0;
                    @endphp

                    <!-- Notification Bell Dropdown with Live Polling -->
                    <div class="relative"
                         x-data="{
                            notifOpen: false,
                            tab: 'semua',
                            unreadCount: {{ $unreadKecCount }},
                            pollUnread() {
                                fetch('{{ route('kecamatan.notifications.unread-count') }}')
                                    .then(res => res.json())
                                    .then(data => {
                                        this.unreadCount = data.count;
                                    })
                                    .catch(() => {});
                            }
                         }"
                         x-init="setInterval(() => pollUnread(), 30000)">

                        <!-- Tombol Lonceng -->
                        <button @click="notifOpen = !notifOpen"
                            class="w-10 h-10 flex items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20 relative transition-colors focus:outline-none">
                            <i class="fa-regular fa-bell text-lg"></i>
                            <template x-if="unreadCount > 0">
                                <span class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white ring-2 ring-[#0C146A] animate-pulse"
                                      x-text="unreadCount">
                                </span>
                            </template>
                        </button>

                        <!-- Notification Dropdown Panel -->
                        <div x-show="notifOpen" @click.away="notifOpen = false" x-cloak
                            class="absolute right-0 mt-3 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-slate-100 py-3 z-50 overflow-hidden text-slate-800">

                            <!-- Header -->
                            <div class="px-4 pb-2 border-b border-slate-100 flex items-center justify-between">
                                <span class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Notifikasi Masuk</span>
                                @if($unreadKecCount > 0)
                                    <form action="{{ route('kecamatan.notifications.read-all') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-[11px] font-bold text-slate-500 hover:text-slate-800 transition-colors">
                                            Tandai Dibaca
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <!-- Poin 1: Tab Filter -->
                            <div class="flex items-center gap-1.5 px-4 py-2 bg-slate-50 border-b border-slate-100 text-xs font-bold text-slate-500">
                                <button type="button"
                                        @click="tab = 'semua'"
                                        :class="tab === 'semua' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60'"
                                        class="px-2.5 py-1 rounded-lg transition-all text-[11px]">
                                    Semua ({{ $allKecNotifs->count() }})
                                </button>
                                <button type="button"
                                        @click="tab = 'unread'"
                                        :class="tab === 'unread' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60'"
                                        class="px-2.5 py-1 rounded-lg transition-all text-[11px] flex items-center gap-1">
                                    Belum Dibaca
                                    <template x-if="unreadCount > 0">
                                        <span class="px-1.5 py-0.2 rounded-full text-[9px] bg-rose-500 text-white" x-text="unreadCount"></span>
                                    </template>
                                </button>
                            </div>

                            <!-- Daftar Notifikasi -->
                            <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto custom-scrollbar">
                                @forelse($allKecNotifs as $notif)
                                    @php $isUnread = is_null($notif->read_at); @endphp
                                    <form action="{{ route('kecamatan.notifications.read', $notif->id) }}" method="POST"
                                          x-show="tab === 'semua' || (tab === 'unread' && {{ $isUnread ? 'true' : 'false' }})">
                                        @csrf
                                        <button type="submit" class="w-full text-left p-3.5 transition-colors flex gap-3 items-start {{ $isUnread ? 'bg-white hover:bg-slate-50' : 'bg-slate-50/50 hover:bg-slate-100/80 opacity-70' }}">
                                            <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5 {{ ($notif->data['tipe'] ?? '') === 'pengajuan_bast' ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600' }}">
                                                <i class="fa-solid {{ ($notif->data['tipe'] ?? '') === 'pengajuan_bast' ? 'fa-clipboard-check' : 'fa-bullhorn' }} text-xs"></i>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between gap-1">
                                                    <div class="text-xs font-bold text-slate-900 leading-snug">{{ $notif->data['judul'] ?? 'Pemberitahuan' }}</div>
                                                    @if($isUnread)
                                                        <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0" title="Belum dibaca"></span>
                                                    @endif
                                                </div>
                                                <div class="text-[11px] text-slate-500 line-clamp-2 mt-0.5">{{ $notif->data['pesan'] ?? '' }}</div>
                                                <div class="text-[10px] text-slate-400 mt-1">{{ $notif->created_at->diffForHumans() }}</div>
                                            </div>
                                        </button>
                                    </form>
                                @empty
                                    <div class="py-8 text-center text-slate-400">
                                        <i class="fa-regular fa-bell-slash text-2xl mb-2 text-slate-300"></i>
                                        <p class="text-xs font-medium">Belum ada notifikasi</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- User Profile Badge & Logout -->
                    <div class="flex items-center gap-3 pl-5 border-l border-white/20">
                        <div class="text-right hidden sm:block">
                            <div class="text-xs font-extrabold text-white">{{ session('kecamatan_name', 'Kecamatan') }}</div>
                            <div class="text-[10px] text-slate-200 font-medium">Admin<br>Kecamatan Jember</div>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center text-white shrink-0">
                            <i class="fa-regular fa-user"></i>
                        </div>

                        <!-- Tombol & Popover Logout di Pojok Kanan Atas -->
                        <div class="relative" x-data="{ logoutOpen: false }">
                            <button type="button"
                                    @click="logoutOpen = !logoutOpen"
                                    title="Keluar / Logout"
                                    :class="logoutOpen ? 'bg-white/20 text-white' : 'text-slate-200 hover:text-white hover:bg-white/10'"
                                    class="flex items-center justify-center w-10 h-10 rounded-lg transition-colors focus:outline-none cursor-pointer">
                                <i class="fa-solid fa-arrow-right-from-bracket text-base"></i>
                            </button>

                            <!-- Backdrop transparan untuk klik di luar -->
                            <div x-show="logoutOpen"
                                 x-cloak
                                 @click="logoutOpen = false"
                                 class="fixed inset-0 z-40"></div>

                            <!-- Popover Profil & Logout (Logo, Nama, Email, Tombol Keluar) -->
                            <div x-show="logoutOpen"
                                 x-cloak
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                 @keydown.escape.window="logoutOpen = false"
                                 class="absolute right-0 mt-3 w-72 sm:w-80 bg-white rounded-2xl shadow-2xl border border-slate-200/90 p-5 z-50 text-slate-800 ring-1 ring-black/5">
                                
                                <div class="relative mb-3">
                                    <!-- Tombol Tutup X -->
                                    <button type="button" @click="logoutOpen = false" class="absolute -top-1 -right-1 w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                                        <i class="fa-solid fa-xmark text-sm"></i>
                                    </button>

                                    <!-- Logo / Avatar Pengguna -->
                                    <div class="w-16 h-16 rounded-full bg-gradient-to-tr from-[#0C146A] to-[#1523BC] text-white flex items-center justify-center mx-auto shadow-md ring-4 ring-slate-100">
                                        <i class="fa-solid fa-landmark text-2xl text-amber-300"></i>
                                    </div>
                                </div>

                                <!-- Nama & Email -->
                                <div class="text-center px-1">
                                    <h4 class="text-sm font-extrabold text-slate-900 leading-snug">{{ $currentKecamatan->nama_lengkap ?? session('kecamatan_name', 'Admin Kecamatan') }}</h4>
                                    <p class="text-xs text-slate-500 mt-0.5 break-all font-medium">{{ $currentKecamatan->email ?? 'kecamatan@gmail.com' }}</p>
                                    <span class="inline-block mt-2 px-2.5 py-0.5 text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 rounded-full">
                                        Kantor Kecamatan Jember
                                    </span>
                                </div>

                                <!-- Tombol Keluar -->
                                <div class="mt-4 pt-3.5 border-t border-slate-100">
                                    <form method="POST" action="{{ route('kecamatan.logout') }}" class="m-0">
                                        @csrf
                                        <button type="submit"
                                                class="w-full py-2.5 px-4 text-xs font-bold text-white bg-red-600 hover:bg-red-700 rounded-xl transition-all shadow-xs flex items-center justify-center gap-2 cursor-pointer">
                                            <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
                                            <span>Keluar dari Akun</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </header>

    <!-- TOAST NOTIFICATION -->
    @if(session('status'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
        class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-4 py-3 rounded-xl shadow-2xl border text-sm font-semibold transition-all transform animate-in slide-in-from-bottom-5 duration-300 {{ session('status_type') == 'success' ? 'bg-emerald-600 text-white border-emerald-500' : (session('status_type') == 'warning' ? 'bg-amber-600 text-white border-amber-500' : 'bg-slate-900 text-white border-slate-800') }}">
        <i class="fa-solid {{ session('status_type') == 'success' ? 'fa-circle-check text-lg' : 'fa-triangle-exclamation text-lg' }}"></i>
        <div>
            <p class="leading-tight">{{ session('status') }}</p>
        </div>
        <button @click="show = false" class="ml-3 text-white/80 hover:text-white">
            <i class="fa-solid fa-xmark text-sm"></i>
        </button>
    </div>
    @endif

    <!-- MAIN BODY CONTENT -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- FOOTER (Matching Figma Exactly) -->
    <footer class="footer-gradient border-t border-white/10 mt-12 py-8 no-print">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <!-- Left -->
                <div>
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-md bg-white/95 flex items-center justify-center text-[#141E71] text-xs shadow-sm">
                            <i class="fa-solid fa-landmark"></i>
                        </div>
                        <h4 class="font-bold text-white text-sm">Pemerintah Kabupaten Jember</h4>
                    </div>
                    <p class="text-xs text-slate-200 mt-1">Dinas Pekerjaan Umum dan Penataan Ruang (PUPR) berkolaborasi dengan Kantor Kecamatan Jember.</p>
                    <p class="text-[11px] text-slate-300 mt-2">© 2026 Portal SIGAP Kabupaten Jember. Hak Cipta Dilindungi Undang-Undang.</p>
                </div>

                <!-- Right -->
                <div class="text-left md:text-right">
                    <p class="text-xs font-semibold text-slate-200">Layanan Darurat Jalan Rusak:</p>
                    <p class="text-sm font-bold text-white mt-0.5">Call Center Kab. Jember: 112 / (021) 879-0123</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- CTRL+K QUICK SEARCH MODAL -->
    <div x-show="searchModalOpen" @keydown.window.ctrl.k.prevent="searchModalOpen = true" @keydown.window.escape="searchModalOpen = false" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-start justify-center pt-20 px-4">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-xl w-full overflow-hidden" @click.away="searchModalOpen = false">
            <div class="p-4 border-b border-slate-100 flex items-center gap-3">
                <i class="fa-solid fa-magnifying-glass text-slate-400 text-lg"></i>
                <input type="text"
                    id="modalSearchInput"
                    placeholder="Ketik nomor tiket (e.g. LP-2026-0842), nama jalan, atau nama pelapor..."
                    class="w-full text-sm font-medium text-slate-800 placeholder-slate-400 focus:outline-none"
                    @keydown.enter="window.location.href = '{{ route('kecamatan.validation.index') }}?search=' + $el.value">
                <kbd class="text-[11px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded font-mono border">ESC</kbd>
            </div>
            <div class="p-3 bg-slate-50 text-xs text-slate-500 flex items-center justify-between">
                <span>Pencarian Cepat Akses Tiket SIGAP</span>
                <span class="text-brand-600 font-semibold cursor-pointer" @click="window.location.href = '{{ route('kecamatan.validation.index') }}'">Buka Semua Antrean &rarr;</span>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>

</html>
