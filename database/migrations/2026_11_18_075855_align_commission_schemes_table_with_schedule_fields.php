
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commission_schemes', function (Blueprint $table) {
            if (!Schema::hasColumn('commission_schemes', 'commission_rate')) {
                $table->decimal('commission_rate', 6, 4)->default(0.0500);
            }

            if (!Schema::hasColumn('commission_schemes', 'valid_from')) {
                $table->timestamp('valid_from', 6)->nullable();
            }

            if (!Schema::hasColumn('commission_schemes', 'valid_to')) {
                $table->timestamp('valid_to', 6)->nullable();
            }

            if (!Schema::hasColumn('commission_schemes', 'updated_at')) {
                $table->timestamp('updated_at', 6)->nullable();
            }
        });

        // Konversi persentase lama ke format desimal.
        if (Schema::hasColumn('commission_schemes', 'percentage')) {
            DB::table('commission_schemes')->update([
                'commission_rate' => DB::raw('percentage / 100'),
                'valid_from' => DB::raw('created_at'),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('commission_schemes', function (Blueprint $table) {
            $columns = [];

            foreach (['commission_rate', 'valid_from', 'valid_to', 'updated_at'] as $column) {
                if (Schema::hasColumn('commission_schemes', $column)) {
                    $columns[] = $column;
                }
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};