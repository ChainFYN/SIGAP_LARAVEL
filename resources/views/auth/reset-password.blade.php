@extends('warga.layouts.app')

@section('title', 'Reset Kata Sandi - SIGAP')

@section('content')
<div class="min-h-[calc(100vh-280px)] flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <div class="w-14 h-14 rounded-2xl bg-slate-900 flex items-center justify-center text-white mx-auto shadow-md mb-4">
            <i class="fa-solid fa-shield-halved text-2xl text-sky-400"></i>
        </div>
        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Atur Ulang Kata Sandi</h2>
        <p class="text-xs text-slate-500 mt-1">Masukkan kode OTP 6 digit dari email Anda dan buat kata sandi baru.</p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
        <div class="bg-white py-8 px-6 sm:px-10 rounded-2xl border border-slate-200 shadow-xs">

            @if (session('status'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs rounded-xl p-3.5 mb-5 flex items-start gap-2.5">
                    <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5 text-sm"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl p-3.5 mb-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('password.update') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-regular fa-envelope text-xs"></i>
                        </div>
                        <input type="email"
                               name="email"
                               id="email"
                               value="{{ old('email', $email) }}"
                               required
                               placeholder="nama@email.com"
                               class="w-full pl-9 pr-3 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-400 bg-slate-50 text-slate-800 placeholder-slate-400">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="otp" class="block text-xs font-bold text-slate-700">Kode OTP (6 Digit)</label>
                        <span class="text-[11px] text-slate-400">Berlaku 10 menit</span>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-hashtag text-xs"></i>
                        </div>
                        <input type="text"
                               name="otp"
                               id="otp"
                               value="{{ old('otp') }}"
                               required
                               maxlength="6"
                               pattern="[0-9]{6}"
                               inputmode="numeric"
                               autocomplete="one-time-code"
                               placeholder="123456"
                               class="w-full pl-9 pr-3 py-2.5 text-base tracking-[0.3em] font-mono font-bold text-center rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-400 bg-slate-50 text-slate-900 placeholder:tracking-normal placeholder:font-sans placeholder:text-xs placeholder:font-normal">
                    </div>
                    <span class="text-[10px] text-slate-400 block mt-1">Masukkan 6 digit angka yang dikirim ke email Anda</span>
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5">Kata Sandi Baru</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-lock text-xs"></i>
                        </div>
                        <input type="password"
                               name="password"
                               id="password"
                               required
                               placeholder="Minimal 8 karakter"
                               class="w-full pl-9 pr-10 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-400 bg-slate-50 text-slate-800 placeholder-slate-400">
                        <button type="button" onclick="togglePasswordVisibility('password', 'password-toggle-icon-1')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                            <i id="password-toggle-icon-1" class="fa-regular fa-eye text-xs"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1.5">Konfirmasi Kata Sandi Baru</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-lock-open text-xs"></i>
                        </div>
                        <input type="password"
                               name="password_confirmation"
                               id="password_confirmation"
                               required
                               placeholder="Ulangi kata sandi baru"
                               class="w-full pl-9 pr-10 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-400 bg-slate-50 text-slate-800 placeholder-slate-400">
                        <button type="button" onclick="togglePasswordVisibility('password_confirmation', 'password-toggle-icon-2')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                            <i id="password-toggle-icon-2" class="fa-regular fa-eye text-xs"></i>
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition shadow-xs flex items-center justify-center gap-2">
                        <span>Perbarui Kata Sandi</span>
                        <i class="fa-solid fa-check text-xs"></i>
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-6 border-t border-slate-100 flex items-center justify-between text-xs">
                <a href="{{ route('password.request') }}" class="text-slate-500 hover:text-slate-800 transition">
                    <i class="fa-solid fa-rotate-right mr-1"></i>Kirim ulang OTP
                </a>
                <a href="{{ route('warga.login') }}" class="font-semibold text-slate-700 hover:text-slate-900 transition">
                    Kembali ke Masuk
                </a>
            </div>

        </div>
    </div>
</div>

<script>
    function togglePasswordVisibility(fieldId, iconId) {
        const input = document.getElementById(fieldId);
        const icon = document.getElementById(iconId);
        if (!input || !icon) return;

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endsection
