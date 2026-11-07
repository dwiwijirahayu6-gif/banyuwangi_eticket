<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            if (! Schema::hasColumn('ticket_types', 'category')) {
                $table->string('category')->default('Tiket Masuk')->after('name');
            }
            if (! Schema::hasColumn('ticket_types', 'description')) {
                $table->string('description', 500)->nullable()->after('category');
            }
            if (! Schema::hasColumn('ticket_types', 'photo_path')) {
                $table->string('photo_path')->nullable()->after('description');
            }
            if (! Schema::hasColumn('ticket_types', 'stock_qty')) {
                $table->integer('stock_qty')->default(100)->after('price');
            }
            if (! Schema::hasColumn('ticket_types', 'prep_minutes')) {
                $table->integer('prep_minutes')->default(5)->after('stock_qty');
            }
            if (! Schema::hasColumn('ticket_types', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
            if (! Schema::hasColumn('ticket_types', 'deleted_at')) {
                $table->softDeletes('deleted_at', 6)->after('updated_at');
            }
        });

        if (! Schema::hasTable('ticket_stock_movements')) {
            Schema::create('ticket_stock_movements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('ticket_type_id')->constrained('ticket_types')->cascadeOnDelete();
                $table->string('idempotency_key', 64)->unique();
                $table->string('type', 32);
                $table->integer('quantity_delta');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_stock_movements');

        Schema::table('ticket_types', function (Blueprint $table) {
            $columns = ['category', 'description', 'photo_path', 'stock_qty', 'prep_minutes', 'updated_at', 'deleted_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('ticket_types', $column)) {
                    if ($column === 'deleted_at') {
                        $table->dropSoftDeletes('deleted_at');
                    } else {
                        $table->dropColumn($column);
                    }
                }
            }
        });
    }
};
