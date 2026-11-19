<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            if (! Schema::hasColumn('destinations', 'facilities')) {
                $table->text('facilities')->nullable()->after('operating_hours');
            }
            if (! Schema::hasColumn('destinations', 'safety_guidelines')) {
                $table->text('safety_guidelines')->nullable()->after('facilities');
            }
            if (! Schema::hasColumn('destinations', 'gallery')) {
                $table->json('gallery')->nullable()->after('safety_guidelines');
            }
        });

        Schema::table('visit_slots', function (Blueprint $table) {
            if (! Schema::hasColumn('visit_slots', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('quota_remaining');
            }
        });
    }

    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $cols = ['facilities', 'safety_guidelines', 'gallery'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('destinations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('visit_slots', function (Blueprint $table) {
            if (Schema::hasColumn('visit_slots', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
