<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banyuwangi Tourism - E-Ticketing Wisata</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts (Poppins) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col justify-between">

    <!-- 1. NAVBAR / HEADER -->
    <header class="bg-slate-900/90 backdrop-blur-md text-white sticky top-0 z-50 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <!-- Logo Brand -->
            <a href="/" class="flex items-center gap-1 text-xl font-extrabold tracking-tight">
                <span class="text-white">bwi</span><span class="text-blue-500">healing</span><span class="text-white">.id</span>
            </a>
            
            <!-- Menu Navigasi -->
            <nav class="hidden md:flex items-center space-x-8 text-sm font-medium">
                <a href="/" class="text-blue-400 font-semibold transition">Beranda</a>
                <a href="#katalog" class="text-slate-300 hover:text-white transition">Destinasi Wisata</a>
             <a href="{{ route('tickets.info') }}" class="text-slate-300 hover:text-white transition">Informasi Tiket</a>
                <a href="{{ route('bookings.lookup') }}" class="text-slate-300 hover:text-white transition">Pembatalan Tiket</a>
            </nav>

            <!-- Tombol Login/Aksi -->
            <div class="flex items-center gap-3">
                <a href="/login" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2 rounded-full shadow-sm transition">
                    Login/Daftar
                </a>
            </div>
        </div>
    </header>

    <!-- 2. HERO BANNER SECTION -->
    <section class="relative bg-slate-900 text-white min-h-[500px] flex items-center justify-center bg-cover bg-center" 
             style="background-image: linear-gradient(to bottom, rgba(15, 23, 42, 0.6), rgba(15, 23, 42, 0.85)), url('images/ijen_beranda.jpeg');">
        
        <div class="max-w-5xl mx-auto px-6 text-center space-y-4 pt-10 pb-24">
            <!-- Badge / Tagline Kecil -->
            <span class="inline-block bg-white/10 backdrop-blur-md text-slate-200 text-xs px-3.5 py-1 rounded-full border border-white/20 font-medium">
                Banyuwangi tourism
            </span>

            <!-- Judul Utama -->
            <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight leading-tight">
                Kepingan surga <br class="hidden md:block"> di ujung jawa
            </h1>
            
            <p class="text-slate-300 text-sm md:text-base max-w-2xl mx-auto">
                Pesan tiket resmi destinasi wisata favorit di Kabupaten Banyuwangi secara cepat, mudah, dan terpercaya.
            </p>
        </div>

        <!-- FLOATING SEARCH BAR OVERLAY -->
        <div class="absolute -bottom-10 left-1/2 -translate-x-1/2 w-full max-w-5xl px-4 z-20">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-100 p-4 md:p-6 text-slate-800">
                <form action="#katalog" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-center">
                    
                    <!-- Input 1: Cari Wisata -->
                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Cari Wisata</label>
                        <input type="text" name="search" placeholder="Air Terjun Lider" 
                               value="Air Terjun Lider"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm font-medium focus:outline-none focus:border-blue-500">
                    </div>

                    <!-- Input 2: Pilih Tanggal -->
                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Pilih Tanggal</label>
                        <input type="text" placeholder="Hari Ini, 24/09/2026" 
                               value="Hari Ini, 24/09/2026"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm font-medium focus:outline-none focus:border-blue-500">
                    </div>

                    <!-- Input 3: Jumlah Pengunjung -->
                    <div class="space-y-1">
                        <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Jumlah Pengunjung</label>
                        <select class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-sm font-medium focus:outline-none focus:border-blue-500">
                            <option>02 orang</option>
                            <option>01 orang</option>
                            <option>03+ orang</option>
                        </select>
                    </div>

                    <!-- Tombol Cari Tiket -->
                    <div class="pt-2 md:pt-5">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-6 rounded-xl shadow-md transition flex items-center justify-center gap-2 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            Cari Tiket
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </section>

    <!-- 3. SECTION WELCOME TO BANYUWANGI TOURISM -->
    <section class="max-w-7xl mx-auto px-6 pt-28 pb-16 w-full">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            
            <!-- Teks Kiri -->
            <div class="space-y-5">
                <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 leading-snug">
                    Welcome to banyuwangi <br><span class="text-blue-600">tourism</span>
                </h2>
                
                <p class="text-slate-600 text-sm leading-relaxed">
                    Destinasi impian bagi para pencinta petualangan, ketenangan alam, dan keunikan budaya. Banyuwangi menyuguhkan paket lengkap liburan yang tak akan Anda temukan di tempat lain.
                </p>

                <p class="text-slate-600 text-sm leading-relaxed">
                    <strong>The Sunrise of Java.</strong> Jadikan liburan Anda kali ini penuh makna dan cerita indah untuk dibawa pulang. Selamat menikmati liburan di Banyuwangi!
                </p>

                <div class="pt-2">
                    <a href="#katalog" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-5 py-3 rounded-full transition shadow-sm">
                        Siap Mengexplore Banyuwangi?
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                </div>
            </div>

            <!-- Grid 4 Gambar Kanan -->
            <div class="grid grid-cols-2 gap-4">
                <div class="overflow-hidden rounded-2xl shadow-sm h-40 md:h-48">
                    <img src="images/bedil_welcome.jpeg" class="w-full h-full object-cover hover:scale-105 transition duration-300">
                </div>
                <div class="overflow-hidden rounded-2xl shadow-sm h-40 md:h-48">
                    <img src="images/bangsring_welcome.jpeg" class="w-full h-full object-cover hover:scale-105 transition duration-300">
                </div>
                <div class="overflow-hidden rounded-2xl shadow-sm h-40 md:h-48">
                    <img src="images/baluran.jpeg">
                </div>
                <div class="overflow-hidden rounded-2xl shadow-sm h-40 md:h-48">
                    <img src="images/jagir_welcome.jpeg" class="w-full h-full object-cover hover:scale-105 transition duration-300">
                </div>
            </div>

        </div>
    </section>

    <!-- 4. SECTION DESTINASI WISATA BANYUWANGI -->
    <section id="katalog" class="max-w-7xl mx-auto px-6 py-12 w-full">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900">
                Destinasi Wisata Banyuwangi
            </h2>
            <a href="#katalog" class="text-xs font-semibold text-blue-600 hover:text-blue-800">Lihat Semua &rarr;</a>
        </div>

        <!-- Grid Kartu Wisata Dinamis -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            @forelse($destinations as $dest)
          <div class="group relative rounded-2xl overflow-hidden shadow-md bg-slate-900 h-72 cursor-pointer transition hover:-translate-y-1" onclick="window.location='{{ route('destinations.show', $dest) }}'">
                <!-- Gambar Latar Belakang -->
                <img src="{{ asset($dest->image) }}" 
                 alt="{{ $dest->name }}" 
                 class="w-full h-full object-cover opacity-85 group-hover:scale-110 transition duration-500">
                
                <!-- Gradient Overlay -->
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/20 to-transparent"></div>

                <!-- Label Jam Operasional --> 
                <div class="absolute top-3 right-3 bg-black/50 backdrop-blur-md text-white text-[10px] px-2.5 py-1 rounded-full border border-white/20">
                    {{ $dest->operating_hours }}
                </div>

                <!-- Informasi di Bagian Bawah Kartu -->
                <div class="absolute bottom-0 left-0 right-0 p-5 space-y-1">
                    <h3 class="text-lg font-bold text-white group-hover:text-blue-400 transition">
                        {{ $dest->name }}
                    </h3>
                    <p class="text-xs text-slate-300 font-medium">Banyuwangi</p>
                    <div class="pt-2 flex justify-between items-center">
                        <span class="text-[11px] text-slate-400">Pengelola: {{ $dest->tenant->name ?? 'Dinas' }}</span>
                     <a href="{{ route('destinations.show', $dest) }}" class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1.5 rounded-lg font-semibold shadow-sm transition">
                   Pesan Tiket
                    </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-3 text-center py-12 text-slate-500 text-sm bg-white rounded-2xl border border-slate-200">
                Belum ada data destinasi wisata di database.
            </div>
            @endforelse
        </div>
    </section>

    <!-- 5. FOOTER -->
    <footer class="bg-slate-900 text-slate-400 py-8 text-center text-xs border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-6 space-y-3">
            <p class="font-semibold text-slate-300">bwihealing.id &bull; E-Ticketing Wisata Kabupaten Banyuwangi</p>
            <p>&copy; 2026 Pemkab Banyuwangi. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>