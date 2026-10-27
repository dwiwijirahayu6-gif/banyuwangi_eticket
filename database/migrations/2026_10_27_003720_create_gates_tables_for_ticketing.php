<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Gates (Gerbang Masuk Wisata)
        Schema::create('gates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destination_id')->constrained('destinations')->restrictOnDelete();
            $table->string('code', 30);
            $table->string('label', 60); // Contoh: "Gerbang Utama", "Pintu Timur"
            $table->string('zone', 60)->nullable(); // Contoh: "Area Parkir", "Dermaga"
            $table->string('status', 20)->default('active');
            $table->timestamps(6);
            $table->unique(['destination_id', 'code']);
        });

        // 2. Tabel Gate QR Tokens
        Schema::create('gate_qr_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gate_id')->constrained('gates')->cascadeOnDelete();
            $table->binary('token_hash', 32); // varbinary(32)
            $table->string('status', 20)->default('active');
            $table->timestamp('issued_at', 6)->nullable();
            $table->timestamp('expires_at', 6)->nullable();
            $table->timestamp('revoked_at', 6)->nullable();
            $table->timestamps(6);
            $table->unique('token_hash');
            $table->index(['gate_id', 'status']);
        });

        // 3. Tabel Customer Sessions (Sesi Anonim Pelanggan)
        Schema::create('customer_sessions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignId('destination_id')->constrained('destinations')->restrictOnDelete();
            $table->foreignId('gate_id')->nullable()->constrained('gates')->nullOnDelete();
            $table->foreignId('qr_token_id')->nullable()->constrained('gate_qr_tokens')->nullOnDelete();
            $table->binary('session_token_hash', 32);
            $table->string('status', 20)->default('active');
            $table->timestamp('expires_at', 6)->nullable();
            $table->timestamps(6);
            $table->unique('session_token_hash');
            $table->index(['destination_id', 'status']);
        });

        // 4. Tabel Tenant Operating Hours (Jam Operasional)
        Schema::create('tenant_operating_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 0=Minggu, 6=Sabtu
            $table->time('opens_at');
            $table->time('closes_at');
            $table->timestamps();
            $table->unique(['tenant_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_sessions');
        Schema::dropIfExists('gate_qr_tokens');
        Schema::dropIfExists('gates');
        Schema::dropIfExists('tenant_operating_hours');
    }
};