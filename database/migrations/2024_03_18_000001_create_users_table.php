<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->date('birth_date'); // Required for age verification and minor account management
            $table->enum('role', ['client', 'admin'])->default('client'); // Role-based access control
            $table->rememberToken();
            $table->timestamps();
            
            $table->index(['email', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
