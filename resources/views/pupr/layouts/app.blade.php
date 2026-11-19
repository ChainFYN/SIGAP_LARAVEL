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
                    <div class="relative">
                        <button class="w-10 h-10 flex items-center justify-center rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200">
                            <i class="fa-regular fa-bell text-lg"></i>
                        </button>
                        <span class="absolute top-0 right-0 w-3.5 h-3.5 bg-amber-500 border-2 border-white rounded-full"></span>
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

                        <form method="POST" action="{{ route('pupr.logout') }}"
                            onsubmit="return confirm('Apakah Anda yakin ingin keluar dari akun PUPR?')">
                            @csrf
                            <button type="submit" title="Keluar / Logout"
                                    class="text-slate-100 hover:text-white p-2 rounded-lg hover:bg-white/10 transition-colors">
                                <i class="fa-solid fa-arrow-right-from-bracket text-base"></i>
                            </button>
                        </form>
                    </div>

                        <!-- Profile Dropdown -->
                        <div x-show="profileOpen" @click.away="profileOpen = false" x-cloak
                             class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-xl border border-slate-200 py-2 z-50 animate-in fade-in slide-in-from-top-2 duration-150">
                            <form method="POST" action="{{ route('pupr.logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 font-semibold hover:bg-red-50 hover:text-red-700 transition-colors flex items-center gap-2">
                                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="flex-1 bg-slate-50">
        @if(session('success'))
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 pt-6">
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 text-xs">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 pt-6">
            <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-semibold flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-triangle-exclamation text-red-600 text-lg"></i>
                    <span>{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.parentElement.remove()" class="text-red-600 hover:text-red-800 text-xs">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
        @endif

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
