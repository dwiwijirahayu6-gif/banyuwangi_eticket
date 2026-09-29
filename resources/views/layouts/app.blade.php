<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'E-Ticketing Wisata') - Banyuwangi Tourism</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">

    <header class="bg-slate-900/90 backdrop-blur-md text-white sticky top-0 z-50 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <a href="{{ route('home') }}" class="flex items-center gap-1 text-xl font-extrabold tracking-tight">
                <span class="text-white">bwi</span><span class="text-blue-500">healing</span><span class="text-white">.id</span>
            </a>

            <nav class="flex items-center space-x-5 md:space-x-8 text-xs md:text-sm font-medium">
                <a href="{{ route('home') }}" class="text-slate-300 hover:text-white transition">Beranda</a>
                <a href="{{ route('home') }}#katalog" class="hidden md:inline text-slate-300 hover:text-white transition">Destinasi Wisata</a>
                <a href="{{ route('tickets.info') }}" class="{{ request()->routeIs('tickets.info') ? 'text-blue-400 font-semibold' : 'text-slate-300 hover:text-white' }} transition">Informasi Tiket</a>
                <a href="{{ route('bookings.lookup') }}" class="{{ request()->routeIs('bookings.*') ? 'text-blue-400 font-semibold' : 'text-slate-300 hover:text-white' }} transition">Pembatalan Tiket</a>
            </nav>
        </div>
    </header>

    <main class="flex-1 w-full">
        <div class="max-w-5xl mx-auto px-6 pt-6 space-y-3">
            @if (session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-xl px-4 py-3">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 text-sm rounded-xl px-4 py-3">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-800 text-sm rounded-xl px-4 py-3">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        @yield('content')
    </main>

    <footer class="bg-slate-900 text-slate-400 py-8 text-center text-xs border-t border-slate-800 mt-12">
        <div class="max-w-7xl mx-auto px-6 space-y-3">
            <p class="font-semibold text-slate-300">bwihealing.id &bull; E-Ticketing Wisata Kabupaten Banyuwangi</p>
            <p>&copy; 2026 Pemkab Banyuwangi. All rights reserved.</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>