
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('commission_schemes')) {
            Schema::create('commission_schemes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
                $table->decimal('commission_rate', 6, 4)->default(5.0000);
                $table->timestamp('valid_from', 6)->nullable();
                $table->timestamp('valid_to', 6)->nullable();
                $table->timestamps(6);
            });
        }
    }

    public function down(): void
    {
        // Tidak menghapus tabel lama.
    }
};
