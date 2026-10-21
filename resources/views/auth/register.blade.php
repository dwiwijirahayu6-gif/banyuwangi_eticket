<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Banyuwangi Tourism E-Ticket</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Poppins', sans-serif; } </style>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4 bg-cover bg-center relative" style="background-image: url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?q=80&w=1200');">

    <!-- Overlay Gelap Background -->
    <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-xs"></div>

    <!-- Container Kartu Register -->
    <div class="relative z-10 w-full max-w-md p-8 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-white/20 my-8">
        
        <!-- Logo & Header -->
        <div class="text-center mb-6">
            <a href="{{ route('home') }}" class="inline-block text-2xl font-extrabold tracking-tight mb-1">
                <span class="text-slate-900">bwi</span><span class="text-blue-600">healing</span><span class="text-slate-900">.id</span>
            </a>
            <h2 class="text-lg font-bold text-slate-800">Buat Akun Baru</h2>
            <p class="text-xs text-slate-500">Daftar untuk mulai memesan tiket wisata Banyuwangi</p>
        </div>

        <!-- Alert Error jika ada validation error -->
        @if ($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl p-3">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form Registrasi -->
        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <!-- Nama Lengkap -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus
                    class="w-full px-4 py-2.5 text-sm bg-slate-100 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                    placeholder="Nama Lengkap Anda">
            </div>

            <!-- Email -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Alamat Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="w-full px-4 py-2.5 text-sm bg-slate-100 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                    placeholder="nama@email.com">
            </div>

            <!-- Password -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Password</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-2.5 text-sm bg-slate-100 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                    placeholder="Minimal 8 karakter">
            </div>

            <!-- Konfirmasi Password -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" required
                    class="w-full px-4 py-2.5 text-sm bg-slate-100 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all"
                    placeholder="Ulangi password">
            </div>

            <!-- Tombol Register -->
            <button type="submit"
                class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl shadow-lg shadow-blue-500/30 transition-all mt-2">
                Daftar Akun
            </button>
        </form>

        <!-- Footer Link Login -->
        <div class="text-center mt-6 text-xs text-slate-500">
            Sudah punya akun? 
            <a href="{{ route('login') }}" class="text-blue-600 font-semibold hover:underline">Masuk di sini</a>
        </div>
    </div>

</body>
</html>