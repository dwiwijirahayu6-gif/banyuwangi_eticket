<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 50)->unique();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone', 50);
            $table->decimal('total_amount', 19, 4);

            $table->enum('status', [
                'draft',
                'pending_payment',
                'paid',
                'completed',
                'cancelled',
                'expired',
            ])->default('draft');

            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->onDelete('cascade');
            $table->foreignId('tenant_id')
                ->constrained('tenants')
                ->onDelete('cascade');
            $table->foreignId('ticket_type_id')
                ->constrained('ticket_types');

            $table->unsignedBigInteger('visit_slot_id');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price_snapshot', 19, 4);
            $table->decimal('subtotal', 19, 4);

            $table->foreign('visit_slot_id', 'fk_booking_items_visit_slot')
                ->references('id')
                ->on('visit_slots');
        });

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_item_id')->constrained('booking_items')->onDelete('cascade');
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('ticket_code')->unique();
            $table->enum('status', ['issued', 'checked_in', 'expired', 'cancelled'])->default('issued');
            $table->date('visit_date');
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->onDelete('cascade');

            $table->string('payment_reference', 100)->unique();
            $table->text('qris_payload')->nullable();
            $table->decimal('gross_amount', 19, 4);

            $table->enum('status', [
                'pending',
                'settlement',
                'expire',
                'failed',
                'cancel',
            ])->default('pending');

            $table->timestamp('created_at')->useCurrent();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('booking_items');
        Schema::dropIfExists('bookings');
    }
};
