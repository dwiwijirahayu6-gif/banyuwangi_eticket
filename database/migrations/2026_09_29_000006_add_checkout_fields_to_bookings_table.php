<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->date('visit_date')->nullable()->after('customer_phone');
            $table->timestamp('expires_at')->nullable()->after('status');
            $table->timestamp('paid_at')->nullable()->after('expires_at');
            $table->timestamp('cancelled_at')->nullable()->after('paid_at');
            $table->string('cancel_reason')->nullable()->after('cancelled_at');
        });

        // Isi visit_date untuk pesanan lama dari tanggal kunjungan tiketnya.
        DB::table('bookings')->whereNull('visit_date')->get()->each(function ($booking) {
            $date = DB::table('tickets')->where('booking_id', $booking->id)->min('visit_date');
            if ($date) {
                DB::table('bookings')->where('id', $booking->id)->update(['visit_date' => $date]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['visit_date', 'expires_at', 'paid_at', 'cancelled_at', 'cancel_reason']);
        });
    }
};