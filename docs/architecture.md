# Arsitektur – Sistem Pemesanan Tiket Wisata Banyuwangi

Modular monolith dengan satu database dan satu deployment. Batas domain dibuat eksplisit di `app/Modules/*`. Diperkenalkan pada Pertemuan 2.

## Modul & Tanggung Jawab

| Modul | Tanggung Jawab | Diisi |
|-------|----------------|-------|
| `Admin` | Administrasi destinasi wisata, pengelola, role, komisi, rekening. | Modul 5 |
| `Catalog` | Destinasi wisata, jenis tiket, harga, stok, katalog publik. | Modul 7 |
| `Ordering` | Keranjang, checkout, booking tiket, snapshot. | Modul 8-9 |
| `Payments` | Gateway QRIS, webhook, settlement, split ledger. | Modul 10-11 |
| `Ticketing` | Validasi tiket di pintu masuk, scan QR, status kunjungan. | Modul 12 |
| `Reporting` | Laporan penjualan tiket, rekonsiliasi, ekspor, withdrawal. | Modul 13 |

## Struktur per Modul (Vertical Slice)

Setiap modul memiliki file fiturnya sendiri di satu folder:
- `{Modul}ServiceProvider.php` — extends `App\Modules\ModuleServiceProvider`
- `Http/Controllers/` — controller milik modul
- `Actions/`, `Data/`, `Services/` — logika aplikasi
- `routes/{customer,tenant,admin}.php` — route fitur, dibungkus `PortalRoutes`
- `resources/views/` — view (`view('alias::...')`)
- `resources/views/livewire/` — komponen Livewire (`<livewire:alias::... />`)

Provider terdaftar di `bootstrap/providers.php`.

## Shared Kernel (Tetap di Lokasi Standar Laravel)

- Model Eloquent di `app/Models`
- Middleware di `app/Http/Middleware`
- Layout & komponen UI di `resources/views/components`
- Helper routing di `app/Support/Routing`

## Routing per Konteks

| Konteks | File | Prefix | Name | Middleware |
|---------|------|--------|------|------------|
| Pelanggan (publik) | `routes/customer.php` | `wisata/{destination}` | `customer.*` | `web` |
| Pengelola Wisata | `routes/tenant.php` | `tenant/{tenant}` | `tenant.*` | `web, auth, verified, role:tenant` |
| Admin Sistem | `routes/admin.php` | `admin` | `admin.*` | `web, auth, verified, role:admin` |

Grup tiap portal didefinisikan tunggal di `App\Support\Routing\PortalRoutes`.

## Authorization

- `auth` menolak tamu
- Middleware `role:{admin|tenant}` menolak role yang tidak cocok dengan **403**
- Menu tersembunyi bukan pengaman; setiap route internal tetap diuji akses langsung
- Kontrak role interim: kolom `users.role` (enum) + `users.status` + `User::hasRole()`
- Mapping: `'admin'` → `'super_admin'`, `'tenant'` → `'tenant_admin'`

## Layout & Komponen UI

- `x-layouts.customer` — mobile-first
- `x-layouts.tenant` — sidebar dengan Alpine.js
- `x-layouts.admin` — tabel responsif
- Komponen: `x-button`, `x-input`, `x-status-badge`, `x-empty-state`

## Konvensi

- Namespace: `App\Modules\{Modul}\...`
- Alias view/Livewire = nama modul huruf kecil (`catalog::`, `ordering::`, ...)
- Nama route: `{konteks}.{aksi}` — unik
- Blade `{{ }}` selalu ter-escape