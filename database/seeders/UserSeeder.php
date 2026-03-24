<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@almadarbank.com',
            'password' => Hash::make('password123'),
            'birth_date' => '1980-01-01',
            'role' => 'admin',
        ]);

        // Adult client
        User::create([
            'first_name' => 'Ahmed',
            'last_name' => 'Mohammed',
            'email' => 'ahmed@example.com',
            'password' => Hash::make('password123'),
            'birth_date' => '1990-05-15',
            'role' => 'client',
        ]);

        // Adult client (potential guardian)
        User::create([
            'first_name' => 'Fatima',
            'last_name' => 'Alami',
            'email' => 'fatima@example.com',
            'password' => Hash::make('password123'),
            'birth_date' => '1985-08-20',
            'role' => 'client',
        ]);

        // Minor client
        User::create([
            'first_name' => 'Youssef',
            'last_name' => 'Mohammed',
            'email' => 'youssef@example.com',
            'password' => Hash::make('password123'),
            'birth_date' => '2010-03-10',
            'role' => 'client',
        ]);

        // Another adult client for joint accounts
        User::create([
            'first_name' => 'Karim',
            'last_name' => 'Bensalem',
            'email' => 'karim@example.com',
            'password' => Hash::make('password123'),
            'birth_date' => '1988-12-05',
            'role' => 'client',
        ]);
    }
}
