# Sistem Pemesanan Tiket Wisata Banyuwangi Berbasis Web

## 1. Deskripsi Proyek

Sistem Pemesanan Tiket Wisata Banyuwangi Berbasis Web adalah aplikasi untuk membantu pengunjung melihat destinasi wisata, memilih jenis tiket, melakukan pemesanan, dan memantau status pembayaran serta tiket.

Aplikasi dikembangkan menggunakan Laravel dan basis data relasional. Sistem juga mengelola data tenant atau pengelola wisata, destinasi, jenis tiket, booking, pembayaran, dan tiket kunjungan.

## 2. Teknologi yang Digunakan

- **Backend:** PHP dan Laravel 13.
- **Database:** MariaDB.
- **Frontend:** Blade, JavaScript, Tailwind CSS, dan Vite.
- **Dependency manager backend:** Composer.
- **Dependency manager frontend:** npm.
- **Pengujian:** PHPUnit/Pest melalui perintah Artisan dan Laravel Testing.

## 3. Persyaratan Sistem

Pastikan perangkat telah memiliki:

- PHP 8.4 atau versi yang sesuai dengan konfigurasi proyek.
- Composer.
- Node.js dan npm.
- MariaDB.
- Git.
- Ekstensi PHP yang diperlukan Laravel dan driver database yang digunakan.

## 4. Instalasi Proyek

Clone repositori proyek, kemudian masuk ke direktori proyek:

```bash
git clone https://github.com/dwiwijirahayu6-gif/banyuwangi_eticket.git
cd banyuwangi_eticket
```

Pasang dependency backend dan frontend:

```bash
composer install
npm install
```

Salin konfigurasi lingkungan:

```bash
copy .env.example .env
```

Pada Linux/macOS, gunakan `cp .env.example .env`.

Buat application key:

```bash
php artisan key:generate
```

## 5. Konfigurasi Lingkungan

Buka file `.env`, kemudian sesuaikan konfigurasi database dengan lingkungan lokal:

```dotenv
APP_NAME="Banyuwangi E-Ticket"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=banyuwangi_eticket
DB_USERNAME=root
DB_PASSWORD=
```

Sesuaikan nama database, username, dan password dengan konfigurasi masing-masing perangkat.

**Penting:** jangan memasukkan file `.env` ke repositori. Sebelum menjalankan migration, pastikan database tujuan benar-benar database baru atau database pengujian yang terisolasi. Jangan menjalankan `migrate:fresh` pada database yang berisi data booking.

## 6. Menjalankan Aplikasi

Jalankan server Laravel:

```bash
php artisan serve
```

Pada terminal terpisah, jalankan Vite selama pengembangan:

```bash
npm run dev
```

Buka alamat aplikasi yang ditampilkan oleh Artisan, biasanya `http://127.0.0.1:8000`.

Untuk membuat aset frontend produksi:

```bash
npm run build
```

## 7. Pengujian dan Pemeriksaan Kode

Jalankan pengujian otomatis:

```bash
php artisan test
```

Periksa format kode PHP:

```bash
vendor/bin/pint --test
```

Build aset frontend:

```bash
npm run build
```

Jalankan seluruh perintah pengujian dan pemeriksaan setelah melakukan perubahan kode. Catat hasil aktual dari pengujian otomatis, pemeriksaan format kode, dan build frontend sebelum melakukan commit.

## 8. Fitur Utama

- Menampilkan destinasi wisata Banyuwangi.
- Menampilkan jenis dan harga tiket.
- Membuat pemesanan tiket.
- Mengelola status pembayaran.
- Menerbitkan tiket setelah pembayaran berhasil.
- Membatalkan atau mengakhiri booking sesuai aturan sistem.
- Mengelola kuota kunjungan berdasarkan tanggal.

## 9. Troubleshooting

**Dependency frontend tidak ditemukan**

Jalankan:

```bash
npm install
```

Kemudian ulangi `npm run build`.

**Koneksi database gagal**

Periksa konfigurasi `DB_*` pada `.env`, pastikan layanan MariaDB berjalan, dan pastikan database tujuan tersedia.

**Pengujian gagal**

Periksa pesan error dan konfigurasi lingkungan pengujian. Jangan menggunakan database produksi atau database berisi data penting untuk pengujian destruktif.

**Perubahan migration tidak sesuai dengan database**

Periksa urutan migration, foreign key, kolom timestamp, serta struktur database tujuan sebelum menerapkan perubahan.

## 10. Keamanan dan Pengelolaan Repositori

- Jangan commit `.env`, password, token, atau kredensial.
- Simpan `.env.example` tanpa nilai rahasia.
- Tinjau `git status` dan perubahan kode sebelum commit.
- Gunakan database terisolasi untuk pengujian migration.
- Buat backup sebelum melakukan perubahan skema pada database yang berisi data penting.

## 11. Status Pengembangan

Proyek sedang dikembangkan dan diuji secara bertahap. Dokumentasi ini perlu diperbarui apabila ada perubahan teknologi, konfigurasi, fitur, atau prosedur instalasi.
