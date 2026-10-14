<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Destination;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. TENANTS (PENGELOLA)
        $tenantLider = Tenant::create([
            'name' => 'Pengelola Air Terjun Lider',
            'code' => 'T-LIDER',
            'status' => 'active',
        ]);

        $tenantIjen = Tenant::create([
            'name' => 'Pengelola Kawah Ijen Banyuwangi',
            'code' => 'T-IJEN',
            'status' => 'active',
        ]);

        $tenantPulauMerah = Tenant::create([
            'name' => 'Pengelola Pantai Pulau Merah',
            'code' => 'T-PUMER',
            'status' => 'active',
        ]);

        // 2. USERS ACCORDING TO ROLE
        User::create([
            'tenant_id' => null,
            'name' => 'Super Admin Dinas Pariwisata',
            'email' => 'admin@banyuwangikab.go.id',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
        ]);

        User::create([
            'tenant_id' => $tenantLider->id,
            'name' => 'Admin Air Terjun Lider',
            'email' => 'lider@banyuwangieticket.id',
            'password' => Hash::make('password123'),
            'role' => 'tenant_admin',
        ]);

        User::create([
            'tenant_id' => $tenantLider->id,
            'name' => 'Petugas Gerbang Lider',
            'email' => 'gate.lider@banyuwangieticket.id',
            'password' => Hash::make('password123'),
            'role' => 'field_officer',
        ]);

        // 3. DESTINATIONS & TICKET TYPES

        // Destinasi 1: Air Terjun Lider
        $destLider = Destination::create([
            'tenant_id' => $tenantLider->id,
            'name' => 'Air Terjun Lider',
            'slug' => 'air-terjun-lider',
            'image' => 'images/lider_katalog.jpeg', // File gambar di public/images/lider.jpg
            'description' => 'Air terjun tertinggi di Banyuwangi dengan keasrian alam pegunungan kawasan lereng Gunung Raung.',
            'operating_hours' => '08:00 - 16:00 WIB',
        ]);

        $ticketLiderLokal = TicketType::create([
            'tenant_id' => $tenantLider->id,
            'destination_id' => $destLider->id,
            'name' => 'Tiket Wisatawan Nusantara (Lokal)',
            'price' => 10000,
        ]);

        // Destinasi 2: Taman Wisata Kawah Ijen
        $destIjen = Destination::create([
            'tenant_id' => $tenantIjen->id,
            'name' => 'Taman Wisata Kawah Ijen',
            'slug' => 'taman-wisata-kawah-ijen',
            'image' => 'images/ijen_katalog.jpeg', // File gambar milik Anda di public/images/ijen_beranda.jpeg
            'description' => 'Pesona kawah asam terbesar di dunia dengan fenomena api biru (Blue Fire) yang langka.',
            'operating_hours' => '01:00 - 12:00 WIB',
        ]);

        TicketType::create([
            'tenant_id' => $tenantIjen->id,
            'destination_id' => $destIjen->id,
            'name' => 'Tiket Wisatawan Nusantara (Lokal)',
            'price' => 15000,
        ]);

        // Destinasi 3: Pantai Pulau Merah
        $destPulauMerah = Destination::create([
            'tenant_id' => $tenantPulauMerah->id,
            'name' => 'Pantai Pulau Merah',
            'slug' => 'pantai-pulau-merah',
            'image' => 'images/pulau_merah_katalog.jpeg', // File gambar di public/images/pulau_merah.jpg
            'description' => 'Pantai indah dengan bukit hijau di tepi pantai dan pemandangan matahari terbenam memukau.',
            'operating_hours' => '06:00 - 18:00 WIB',
        ]);

        TicketType::create([
            'tenant_id' => $tenantPulauMerah->id,
            'destination_id' => $destPulauMerah->id,
            'name' => 'Tiket Masuk Pantai',
            'price' => 10000,
        ]);

        // 4. SAMPLE TRANSACTIONS
        $booking = Booking::create([
            'booking_code' => 'TIX-BWI-20260918-001',
            'customer_name' => 'Wisatawan Contoh',
            'customer_email' => 'wisatawan@gmail.com',
            'customer_phone' => '081234567890',
            'total_amount' => 20000,
            'status' => 'paid',
            'visit_date' => '2026-09-25',
            'total_amount' => 20000,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $bookingItem = BookingItem::create([
            'booking_id' => $booking->id,
            'tenant_id' => $tenantLider->id,
            'ticket_type_id' => $ticketLiderLokal->id,
            'quantity' => 2,
            'unit_price_snapshot' => 10000,
            'subtotal' => 20000,
        ]);

        Ticket::create([
            'booking_item_id' => $bookingItem->id,
            'booking_id' => $booking->id,
            'tenant_id' => $tenantLider->id,
            'ticket_code' => 'TIX-BWI-20260918-001-A',
            'status' => 'issued',
            'visit_date' => '2026-09-25',
        ]);

        Payment::create([
            'booking_id' => $booking->id,
            'payment_reference' => 'QRIS-BWI-20260918-99',
            'qris_payload' => '00020101021226670016ID.GO.QRIS...',
            'gross_amount' => 20000,
            'status' => 'settlement',
        ]);
    }
}
