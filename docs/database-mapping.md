# Database Mapping - Banyuwangi Eticket

## 1. Tujuan

Dokumentasi ini menjelaskan pemetaan tabel database ke model Eloquent pada aplikasi Banyuwangi Eticket. Dokumentasi mencakup fungsi model, relasi antarmodel, pengelolaan tanggal, timestamp, dan integritas data.

## 2. Pemetaan Tabel dan Model

| Tabel | Model | Fungsi |
|---|---|---|
| tenants | Tenant | Data pengelola atau tenant |
| users | User | Data akun pengguna dan peran pengguna |
| destinations | Destination | Informasi destinasi wisata |
| ticket_types | TicketType | Jenis dan harga tiket |
| visit_slots | VisitSlot | Jadwal kunjungan dan kuota |
| bookings | Booking | Data pemesanan |
| booking_items | BookingItem | Rincian tiket dalam pesanan |
| payments | Payment | Informasi pembayaran |
| payment_events | PaymentEvent | Riwayat peristiwa pembayaran |
| tickets | Ticket | Tiket yang diterbitkan |
| audit_logs | AuditLog | Pencatatan aktivitas sistem |
| commission_schemes | CommissionScheme | Skema komisi |
| ledger_entries | LedgerEntry | Pencatatan transaksi keuangan |
| withdrawals | Withdrawal | Pencatatan penarikan dana |

Model yang tercantum di atas telah diperiksa keberadaan berkasnya pada direktori `app/Models`.

## 3. Relasi Antarmodel

### Tenant

Model `Tenant` memiliki relasi `hasMany` ke `Destination`, `User`, `TicketType`, dan `VisitSlot`.

### User

Model `User` memiliki relasi `belongsTo` ke `Tenant` melalui kolom `tenant_id`.

Relasi ini digunakan untuk menghubungkan akun pengguna dengan tenant yang terkait.

### Destination

Model `Destination` memiliki relasi `belongsTo` ke `Tenant` dan `hasMany` ke `TicketType`.

### TicketType

Model `TicketType` memiliki relasi `belongsTo` ke `Tenant` dan `Destination`.

### VisitSlot

Model `VisitSlot` memiliki relasi `belongsTo` ke `Tenant` dan `Destination`, serta `hasMany` ke `BookingItem`.

### Booking

Model `Booking` memiliki relasi `hasMany` ke `BookingItem` dan `Ticket`, serta `hasOne` ke `Payment`.

### BookingItem

Model `BookingItem` memiliki relasi `belongsTo` ke `Booking`, `Tenant`, `TicketType`, dan `VisitSlot`, serta `hasMany` ke `Ticket`.

### Payment

Model `Payment` memiliki relasi `belongsTo` ke `Booking` dan `hasMany` ke `PaymentEvent`.

### PaymentEvent

Model `PaymentEvent` memiliki relasi `belongsTo` ke `Payment`.

### Ticket

Model `Ticket` memiliki relasi `belongsTo` ke `Booking`, `BookingItem`, dan `Tenant`.

Relasi `verifiedBy` mengarah ke model `User` melalui foreign key `verified_by_user_id`.

### AuditLog

Model `AuditLog` memiliki relasi `belongsTo` ke `User`.

### CommissionScheme

Model `CommissionScheme` memiliki relasi `belongsTo` ke `Tenant`.

### LedgerEntry

Model `LedgerEntry` memiliki relasi `belongsTo` ke `Tenant`, `BookingItem`, dan `Withdrawal`.

### Withdrawal

Model `Withdrawal` memiliki relasi `belongsTo` ke `Tenant` dan `User` melalui relasi `approvedBy`. Model ini juga memiliki relasi `hasMany` ke `LedgerEntry`.

## 4. Pengelolaan Tanggal dan Timestamp

Beberapa model menggunakan cast untuk mengubah atribut menjadi tipe data yang sesuai saat diakses melalui Eloquent.

- `Booking`: `visit_date` menggunakan cast `date`; `expires_at`, `paid_at`, dan `cancelled_at` menggunakan cast `datetime`.
- `VisitSlot`: `visit_date` menggunakan cast `date`.
- `Ticket`: `visit_date` menggunakan cast `date`.
- `User`: `email_verified_at` menggunakan cast `datetime`, sedangkan `password` menggunakan cast `hashed`.

Model `Tenant`, `Destination`, `TicketType`, `Booking`, dan `Payment` menggunakan konstanta `UPDATED_AT = null` untuk menonaktifkan pengelolaan otomatis kolom `updated_at`.

Model `BookingItem` dan `VisitSlot` menggunakan `$timestamps = false` untuk menonaktifkan pengelolaan timestamp otomatis oleh Eloquent.

Pengaturan timestamp perlu disesuaikan dengan struktur kolom pada tabel masing-masing.

## 5. Integritas Data

Struktur database menggunakan foreign key untuk menjaga hubungan antartabel.

Contoh hubungan yang telah ditemukan pada skema database:

- `users.tenant_id` mengacu pada `tenants.id`.
- `destinations.tenant_id` mengacu pada `tenants.id`.
- `ticket_types.destination_id` mengacu pada `destinations.id`.
- `ticket_types.tenant_id` mengacu pada `tenants.id`.
- `bookings` berhubungan dengan rincian pesanan, pembayaran, dan tiket.
- `booking_items` berhubungan dengan pemesanan, tenant, jenis tiket, dan jadwal kunjungan.
- `payments` berhubungan dengan `bookings`, sedangkan `payment_events` berhubungan dengan `payments`.

Foreign key, indeks, tipe data, serta aturan penghapusan perlu mengacu pada struktur aktual dalam `docs/database/schema.sql`.

## 6. Factory dan Seeder

Factory pengguna berada pada `database/factories/UserFactory.php`. Factory ini menghasilkan data pengguna untuk kebutuhan pengujian, termasuk nama dan email acak serta role bawaan `tenant_admin`.

Seeder utama berada pada `database/seeders/DatabaseSeeder.php`. Seeder tersebut menyiapkan dua akun demo dengan role `super_admin` dan `tenant_admin`.

Seeder belum dijalankan ulang dalam tahap pemeriksaan ini untuk menghindari perubahan yang tidak disengaja terhadap akun demo yang sudah ada.

## 7. Hasil Verifikasi dan Catatan

Pemeriksaan awal mencakup keberadaan model, deklarasi relasi, struktur tabel, factory, seeder, dan status migrasi.

Setelah relasi `tenant()` ditambahkan ke model `User`, pengujian aplikasi menunjukkan 25 pengujian lulus dengan 92 assertions.

Hasil tersebut menunjukkan bahwa seluruh pengujian yang dijalankan berhasil. Namun, hasil pengujian tidak dengan sendirinya membuktikan bahwa setiap relasi dan seluruh struktur database telah diverifikasi secara menyeluruh.

Dokumentasi ini perlu diperbarui apabila hasil pemeriksaan lanjutan menemukan perbedaan antara model, migrasi, dan struktur database aktual.
