<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_number')->unique(); // Unique account identifier
            $table->enum('type', ['COURANT', 'EPARGNE', 'MINEUR']); // Account types as specified
            $table->enum('status', ['ACTIVE', 'BLOCKED', 'CLOSED'])->default('ACTIVE'); // Account statuses
            $table->decimal('balance', 15, 2)->default(0); // Account balance with precision for financial operations
            $table->decimal('overdraft_limit', 15, 2)->default(0); // Overdraft limit for COURANT accounts
            $table->decimal('interest_rate', 5, 2)->default(0); // Annual interest rate for EPARGNE and MINEUR accounts
            $table->integer('monthly_withdrawal_limit')->default(0); // Withdrawal limits (3 for EPARGNE, 2 for MINEUR)
            $table->integer('monthly_withdrawals_count')->default(0); // Track monthly withdrawals for limits
            $table->date('monthly_withdrawal_reset')->default(now()->startOfMonth()); // Reset counter monthly
            $table->decimal('monthly_fee', 15, 2)->default(0); // Monthly fees for COURANT accounts
            $table->text('block_reason')->nullable(); // Reason when account is blocked
            $table->timestamp('blocked_at')->nullable(); // When account was blocked
            $table->timestamp('closed_at')->nullable(); // When account was closed
            $table->timestamps();
            
            $table->index(['account_number', 'status']);
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
