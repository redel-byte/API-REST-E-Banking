<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $ahmed = User::where('email', 'ahmed@example.com')->first();
        $fatima = User::where('email', 'fatima@example.com')->first();
        $youssef = User::where('email', 'youssef@example.com')->first();
        $karim = User::where('email', 'karim@example.com')->first();

        // Ahmed's Current Account with overdraft
        $currentAccount = Account::create([
            'account_number' => 'ACC001001',
            'type' => 'COURANT',
            'status' => 'ACTIVE',
            'balance' => 5000.00,
            'overdraft_limit' => 1000.00,
            'monthly_fee' => 50.00,
        ]);
        $currentAccount->users()->attach($ahmed->id, ['role' => 'owner']);

        // Fatima's Savings Account with interest
        $savingsAccount = Account::create([
            'account_number' => 'ACC001002',
            'type' => 'EPARGNE',
            'status' => 'ACTIVE',
            'balance' => 15000.00,
            'interest_rate' => 3.5,
            'monthly_withdrawal_limit' => 3,
            'monthly_withdrawals_count' => 1,
        ]);
        $savingsAccount->users()->attach($fatima->id, ['role' => 'owner']);

        // Youssef's Minor Account with guardian Fatima
        $minorAccount = Account::create([
            'account_number' => 'ACC001003',
            'type' => 'MINEUR',
            'status' => 'ACTIVE',
            'balance' => 2000.00,
            'interest_rate' => 2.5,
            'monthly_withdrawal_limit' => 2,
            'monthly_withdrawals_count' => 0,
        ]);
        $minorAccount->users()->attach($youssef->id, ['role' => 'owner']);
        $minorAccount->users()->attach($fatima->id, ['role' => 'guardian']);

        // Joint Account between Ahmed and Karim
        $jointAccount = Account::create([
            'account_number' => 'ACC001004',
            'type' => 'COURANT',
            'status' => 'ACTIVE',
            'balance' => 8000.00,
            'overdraft_limit' => 2000.00,
            'monthly_fee' => 75.00,
        ]);
        $jointAccount->users()->attach($ahmed->id, ['role' => 'owner']);
        $jointAccount->users()->attach($karim->id, ['role' => 'owner']);

        // Another Savings Account for Ahmed
        $savingsAccount2 = Account::create([
            'account_number' => 'ACC001005',
            'type' => 'EPARGNE',
            'status' => 'ACTIVE',
            'balance' => 25000.00,
            'interest_rate' => 4.0,
            'monthly_withdrawal_limit' => 3,
            'monthly_withdrawals_count' => 0,
        ]);
        $savingsAccount2->users()->attach($ahmed->id, ['role' => 'owner']);
    }
}
