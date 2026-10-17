
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            if (!Schema::hasColumn('destinations', 'image')) {
                $table->string('image')->nullable();
            }
        });

        Schema::table('destinations', function (Blueprint $table) {
            $table->string('operating_hours')
                ->default('08:00 - 16:00 WIB')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            if (Schema::hasColumn('destinations', 'image')) {
                $table->dropColumn('image');
            }
        });
    }
};
