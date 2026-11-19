<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('withdrawals')) {
            Schema::create('withdrawals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->decimal('amount', 19, 4);
                $table->enum('status', ['pending', 'approved', 'disbursed', 'rejected'])->default('pending');
                $table->text('bank_account_info');
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (! Schema::hasTable('ledger_entries')) {
            Schema::create('ledger_entries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
                $table->foreignId('booking_item_id')->nullable()->constrained('booking_items')->nullOnDelete();
                $table->foreignId('withdrawal_id')->nullable()->constrained('withdrawals')->nullOnDelete();
                $table->enum('transaction_type', ['credit_sale', 'debit_withdrawal', 'fee_deduction', 'refund']);
                $table->decimal('amount', 19, 4);
                $table->decimal('balance_after', 19, 4);
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        // Safe additive rollback
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('withdrawals');
    }
};
