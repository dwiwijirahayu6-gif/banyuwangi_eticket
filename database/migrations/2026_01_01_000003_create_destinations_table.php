<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('destinations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tenant_id')
                ->constrained('tenants')
                ->onDelete('cascade');

            $table->string('name');
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->string('operating_hours')
                ->default('08:00 - 16:00 WIB');

            $table->timestamp('created_at')->useCurrent();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('destinations');
    }
};
