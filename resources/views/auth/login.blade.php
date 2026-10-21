@extends('layouts.app')

@section('title', 'Masuk Akun')

@section('content')
<div class="py-12 px-4 flex items-center justify-center bg-slate-100/60 min-h-[calc(100vh-16rem)]">
    <div class="w-full max-w-md p-8 bg-white rounded-2xl shadow-xl border border-slate-200">
        <!-- Header Card Login -->
        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-slate-800">Selamat Datang Kembali</h2>
            <p class="text-xs text-slate-500 mt-1">Masuk ke akun bwihealing.id Anda</p>
        </div>

        <!-- Form Login -->
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Email / Username</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                    placeholder="nama@email.com">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Password</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                    placeholder="••••••••">
            </div>

            <div class="flex items-center justify-between text-xs">
                <label class="flex items-center text-slate-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2">Ingat Saya</span>
                </label>
                <a href="#" class="text-blue-600 hover:underline">Lupa Password?</a>
            </div>

            <button type="submit"
                class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl shadow-md shadow-blue-500/20 transition-all">
                Masuk
            </button>
        </form>

        <!-- Link Registrasi -->
        <div class="text-center mt-6 text-xs text-slate-500">
            Belum punya akun? 
            <a href="{{ route('register') }}" class="text-blue-600 font-semibold hover:underline">Daftar Sekarang</a>
        </div>
    </div>
</div>
@endsection