<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['TRANSFER', 'FEE', 'FEE_FAILED', 'INTEREST']); // Transaction types
            $table->decimal('amount', 15, 2); // Transaction amount (positive for credit, negative for debit)
            $table->decimal('balance_before', 15, 2); // Account balance before transaction
            $table->decimal('balance_after', 15, 2); // Account balance after transaction
            $table->string('reference')->unique(); // Unique transaction reference
            $table->text('description')->nullable(); // Transaction description
            $table->enum('status', ['PENDING', 'COMPLETED', 'FAILED'])->default('COMPLETED'); // Transaction status
            $table->text('failure_reason')->nullable(); // Reason for failed transactions
            $table->foreignId('related_transfer_id')->nullable()->constrained('transfers')->onDelete('set null'); // Link to transfer if applicable
            $table->timestamps();
            
            $table->index(['account_id', 'type']);
            $table->index(['account_id', 'status']);
            $table->index(['reference']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
