<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_slots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('destination_id');
            $table->date('visit_date');
            $table->integer('quota_capacity')->default(500);
            $table->integer('quota_remaining')->default(500);

            $table->unique(
                ['destination_id', 'visit_date'],
                'uq_destination_visit_date'
            );

            $table->foreign('tenant_id', 'fk_visit_slots_tenant')
                ->references('id')
                ->on('tenants')
                ->onDelete('cascade');

            $table->foreign('destination_id', 'fk_visit_slots_destination')
                ->references('id')
                ->on('destinations')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_slots');
    }
};
