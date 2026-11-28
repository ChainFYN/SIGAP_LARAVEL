<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Admin PUPR') - SIGAP Kab. Bogor</title>

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
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            900: '#0c4a6e',
                        },
                        navy: {
                            800: '#0f172a',
                            900: '#080f20',
                        }
                    }
                }
            }
        }
    </script>

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.5/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .navy-gradient {
            background: linear-gradient(180deg, #0C146A 0%, #101B93 90%, #1523BC 100%);
            box-shadow: 0 4px 18px rgba(20, 30, 113, 0.18);
        }
        .footer-gradient {
            background: linear-gradient(180deg, #1523BC 0%, #101B93 20%, #0C146A 100%);
            box-shadow: 0 -4px 18px rgba(20, 30, 113, 0.12);
        }
    </style>
</head>
<body class="h-full flex flex-col text-slate-800 antialiased">

    <!-- TOP NAVBAR -->
    <header class="navy-gradient border-b border-white/10 sticky top-0 z-40 shadow-[0_8px_24px_rgba(20,30,113,0.12)]">
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">

                <!-- Left: Logo & Nav -->
                <div class="flex items-center gap-8">
                    <a href="{{ route('pupr.dashboard') }}" class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/95 flex items-center justify-center text-white shadow-md ring-1 ring-white/40">
                            <i class="fa-solid fa-hard-hat text-lg text-yellow-500"></i>
                        </div>
                        <div>
                            <div class="font-extrabold text-xl tracking-tight text-white leading-none">SIGAP</div>
                            <div class="text-[10px] font-bold uppercase tracking-[0.22em] text-cyan-100/90 mt-1">PUPR</div>
                        </div>
                    </a>

                    <nav class="hidden md:flex items-center gap-2">
                        <a href="{{ route('pupr.dashboard') }}" class="px-4 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('pupr.dashboard') ? 'bg-[#F5F5F5] saturate-50 text-[#141E71] shadow-sm' : 'text-slate-100 hover:text-white hover:bg-white/10' }}">
                            Dashboard Operasional
                        </a>

                        <a href="{{ route('pupr.tim-lapangan.index') }}" class="px-4 py-2.5 text-xs font-bold rounded-xl transition-all {{ request()->routeIs('pupr.tim-lapangan.*') ? 'bg-[#F5F5F5] saturate-50 text-[#141E71] shadow-sm' : 'text-slate-100 hover:text-white hover:bg-white/10' }}">
                            Tim Lapangan
                        </a>

                        <a href="{{ route('pupr.riwayat') }}"
                              class="px-4 py-2.5 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('pupr.riwayat') ? 'bg-[#F5F5F5] saturate-50 text-[#141E71] shadow-sm' : 'text-slate-100 hover:text-white hover:bg-white/10' }}">
                               Riwayat Penanganan
                           </a>
                    </nav>
                </div>



                <!-- Right: User Profile -->
                <div class="flex items-center gap-5">
                    @php
                        $currentPupr = \App\Models\Pengguna::find(session('pupr_id'));
                        // Ambil 10 notifikasi terakhir (baik sudah dibaca maupun belum)
                        $allNotifs = $currentPupr ? $currentPupr->notifications()->take(10)->get() : collect();
                        $unreadCount = $currentPupr ? $currentPupr->unreadNotifications()->count() : 0;
                    @endphp

                    <!-- Notification Bell Dropdown with Live Polling -->
                    <div class="relative"
                         x-data="{
                            notifOpen: false,
                            tab: 'semua', // 'semua' atau 'unread'
                            unreadCount: {{ $unreadCount }},
                            // Poin 2: Live Polling setiap 30 detik
                            pollUnread() {
                                fetch('{{ route('pupr.notifications.unread-count') }}')
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
                                class="relative w-10 h-10 flex items-center justify-center rounded-full bg-slate-800 text-slate-300 hover:text-white hover:bg-slate-700 transition-colors focus:outline-none">
                            <i class="fa-regular fa-bell text-lg"></i>
                            <template x-if="unreadCount > 0">
                                <span class="absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[10px] font-bold text-white ring-2 ring-slate-900 animate-pulse"
                                      x-text="unreadCount">
                                </span>
                            </template>
                        </button>

                        <!-- Dropdown Panel -->
                        <div x-show="notifOpen"
                             @click.away="notifOpen = false"
                             x-cloak
                             class="absolute right-0 mt-3 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-slate-100 py-3 z-50 overflow-hidden text-slate-800">

                            <!-- Header Notifikasi & Tombol Tandai Semua Dibaca -->
                            <div class="flex items-center justify-between px-4 pb-2 border-b border-slate-100">
                                <span class="font-extrabold text-slate-900 text-sm">Notifikasi</span>
                                @if($unreadCount > 0)
                                    <form action="{{ route('pupr.notifications.read-all') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-[11px] font-bold text-slate-500 hover:text-slate-800 transition-colors">
                                            Tandai semua dibaca
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <!-- Poin 1: Tab Filter (Semua vs Belum Dibaca) -->
                            <div class="flex items-center gap-1.5 px-4 py-2 bg-slate-50 border-b border-slate-100 text-xs font-bold text-slate-500">
                                <button type="button"
                                        @click="tab = 'semua'"
                                        :class="tab === 'semua' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-200/60'"
                                        class="px-2.5 py-1 rounded-lg transition-all text-[11px]">
                                    Semua ({{ $allNotifs->count() }})
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

                            <!-- Daftar Notifikasi (Riwayat Tidak Lenyap) -->
                            <div class="divide-y divide-slate-50 max-h-80 overflow-y-auto">
                                @forelse($allNotifs as $notif)
                                    @php $isUnread = is_null($notif->read_at); @endphp
                                    <form action="{{ route('pupr.notifications.read', $notif->id) }}" method="POST"
                                          x-show="tab === 'semua' || (tab === 'unread' && {{ $isUnread ? 'true' : 'false' }})">
                                        @csrf
                                        <button type="submit" class="w-full text-left p-3.5 transition-colors flex gap-3 items-start {{ $isUnread ? 'bg-white hover:bg-slate-50' : 'bg-slate-50/50 hover:bg-slate-100/80 opacity-70' }}">
                                            <!-- Icon Tipe -->
                                            <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5
                                                {{ ($notif->data['tipe'] ?? '') === 'bast_ditolak' ? 'bg-rose-100 text-rose-600' :
                                                   (($notif->data['tipe'] ?? '') === 'bast_disetujui' ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600') }}">
                                                <i class="fa-solid
                                                    {{ ($notif->data['tipe'] ?? '') === 'bast_ditolak' ? 'fa-triangle-exclamation' :
                                                       (($notif->data['tipe'] ?? '') === 'bast_disetujui' ? 'fa-circle-check' : 'fa-hard-hat') }} text-xs"></i>
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

                    <!-- User Profile & Logout -->
                    <div class="flex items-center gap-3 pl-5 border-l border-white/15">
                        <div class="text-right hidden sm:block text-slate-100">
                            <div class="text-xs font-extrabold text-white">{{ session('pupr_name', 'Ir. Hendra Gunawan, S.T.') }}</div>
                            <div class="text-[10px] text-cyan-100/90 font-medium">Koordinator UPT<br>Pemeliharaan Jalan Wilayah 1</div>
                        </div>
                        <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-700 shrink-0 shadow-sm ring-1 ring-white/30">
                            <i class="fa-regular fa-user"></i>
                        </div>

                        <!-- Tombol & Popover Logout di Pojok Kanan Atas -->
                        <div class="relative" x-data="{ logoutOpen: false }">
                            <button type="button"
                                    @click="logoutOpen = !logoutOpen"
                                    title="Keluar / Logout"
                                    :class="logoutOpen ? 'bg-white/20 text-white' : 'text-slate-100 hover:text-white hover:bg-white/10'"
                                    class="p-2 rounded-lg transition-colors focus:outline-none cursor-pointer">
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
                                        <i class="fa-solid fa-user-gear text-2xl text-sky-300"></i>
                                    </div>
                                </div>

                                <!-- Nama & Email -->
                                <div class="text-center px-1">
                                    <h4 class="text-sm font-extrabold text-slate-900 leading-snug">{{ $currentPupr->nama_lengkap ?? session('pupr_name', 'Admin PUPR') }}</h4>
                                    <p class="text-xs text-slate-500 mt-0.5 break-all font-medium">{{ $currentPupr->email ?? 'admin@pupr.jemberkab.go.id' }}</p>
                                    <span class="inline-block mt-2 px-2.5 py-0.5 text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 rounded-full">
                                        Dinas PUPR Wilayah 1
                                    </span>
                                </div>

                                <!-- Tombol Keluar -->
                                <div class="mt-4 pt-3.5 border-t border-slate-100">
                                    <form method="POST" action="{{ route('pupr.logout') }}" class="m-0">
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

    <!-- TOAST NOTIFICATION (Pojok Kanan Bawah seperti Warga & Kecamatan) -->
    @if(session('success') || session('status'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-4 py-3 rounded-xl shadow-2xl border text-sm font-semibold transition-all transform animate-in slide-in-from-bottom-5 duration-300 bg-emerald-600 text-white border-emerald-500">
            <i class="fa-solid fa-circle-check text-lg"></i>
            <div>
                <p class="leading-tight">{{ session('success') ?? session('status') }}</p>
            </div>
            <button @click="show = false" class="ml-3 text-white/80 hover:text-white cursor-pointer">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-4 py-3 rounded-xl shadow-2xl border text-sm font-semibold transition-all transform animate-in slide-in-from-bottom-5 duration-300 bg-red-600 text-white border-red-500">
            <i class="fa-solid fa-triangle-exclamation text-lg"></i>
            <div>
                <p class="leading-tight">{{ session('error') }}</p>
            </div>
            <button @click="show = false" class="ml-3 text-white/80 hover:text-white cursor-pointer">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
    @endif

    <!-- MAIN CONTENT -->
    <main class="flex-1 bg-slate-50">
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="footer-gradient border-t border-white/10 mt-12 py-8">
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
                    <p class="text-xs text-slate-200 mt-1">Dinas Pekerjaan Umum dan Penataan Ruang (PUPR) berkolaborasi dengan Kantor Kecamatan & Masyarakat Jember.</p>
                    <p class="text-[11px] text-slate-300 mt-2">&copy; {{ date('Y') }} Portal SIGAP Kabupaten Jember. Hak Cipta Dilindungi Undang-Undang.</p>
                </div>

                <!-- Right -->
                <div class="text-left md:text-right">
                    <p class="text-xs font-semibold text-slate-200">Layanan Darurat Jalan Rusak:</p>
                    <p class="text-sm font-bold text-white mt-0.5">Call Center Kab. Jember: 112 / (0331) 487-123</p>
                    <div class="flex items-center md:justify-end gap-3 text-[11px] text-slate-300 mt-2">
                        <span>Standar Partisipasi Warga</span>
                        <span>&bull;</span>
                        <span class="font-mono bg-white/10 px-1.5 py-0.5 rounded border border-white/15 text-white">Versi 2.4.1</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
