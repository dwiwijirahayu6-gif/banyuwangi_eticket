<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->date('visit_date')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason', 250)->nullable();
        });

        // Isi tanggal kunjungan dari tiket terlebih dahulu.
        DB::table('bookings')
            ->whereNull('visit_date')
            ->orderBy('id')
            ->get()
            ->each(function ($booking) {
                $date = DB::table('tickets')
                    ->where('booking_id', $booking->id)
                    ->min('visit_date');

                if ($date === null) {
                    $date = DB::table('booking_items')
                        ->join(
                            'visit_slots',
                            'booking_items.visit_slot_id',
                            '=',
                            'visit_slots.id'
                        )
                        ->where('booking_items.booking_id', $booking->id)
                        ->min('visit_slots.visit_date');
                }

                if ($date !== null) {
                    DB::table('bookings')
                        ->where('id', $booking->id)
                        ->update(['visit_date' => $date]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'visit_date',
                'expires_at',
                'paid_at',
                'cancelled_at',
                'cancel_reason',
            ]);
        });
    }
};
