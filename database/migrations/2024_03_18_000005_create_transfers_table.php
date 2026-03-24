<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_account_id')->constrained('accounts')->onDelete('cascade');
            $table->foreignId('to_account_id')->constrained('accounts')->onDelete('cascade');
            $table->foreignId('initiated_by_user_id')->constrained('users')->onDelete('cascade'); 
            $table->decimal('amount', 15, 2);
            $table->string('reference')->unique(); 
            $table->text('description')->nullable();
            $table->enum('status', ['PENDING', 'COMPLETED', 'FAILED'])->default('PENDING');
            $table->text('failure_reason')->nullable();
            $table->timestamp('executed_at')->nullable(); 
            $table->timestamps();
            
            $table->index(['from_account_id', 'status']);
            $table->index(['to_account_id', 'status']);
            $table->index(['initiated_by_user_id']);
            $table->index(['reference']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
