@extends('warga.layouts.app')

@section('title', 'Lupa Kata Sandi - SIGAP')

@section('content')
<div class="min-h-[calc(100vh-280px)] flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <div class="w-14 h-14 rounded-2xl bg-slate-900 flex items-center justify-center text-white mx-auto shadow-md mb-4">
            <i class="fa-solid fa-key text-2xl text-amber-400"></i>
        </div>
        <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Lupa Kata Sandi?</h2>
        <p class="text-xs text-slate-500 mt-1">Masukkan email akun Anda. Kami akan mengirimkan 6 digit kode OTP untuk verifikasi.</p>
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

            <form action="{{ route('password.email') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Email Terdaftar</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-regular fa-envelope text-xs"></i>
                        </div>
                        <input type="email"
                               name="email"
                               id="email"
                               value="{{ old('email') }}"
                               required
                               autofocus
                               placeholder="nama@email.com"
                               class="w-full pl-9 pr-3 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-400 bg-slate-50 text-slate-800 placeholder-slate-400">
                    </div>
                    <span class="text-[10px] text-slate-400 block mt-1">Pastikan email aktif dan dapat menerima pesan masuk</span>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition shadow-xs flex items-center justify-center gap-2">
                        <span>Kirim Kode OTP</span>
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-6 border-t border-slate-100 text-center">
                <a href="{{ route('warga.login') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-slate-900 transition">
                    <i class="fa-solid fa-arrow-left text-[11px]"></i>
                    <span>Kembali ke Halaman Masuk</span>
                </a>
            </div>

        </div>
    </div>
</div>
@endsection
