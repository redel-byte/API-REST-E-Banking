<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $currentAccount = Account::where('account_number', 'ACC001001')->first();
        $savingsAccount = Account::where('account_number', 'ACC001002')->first();
        $minorAccount = Account::where('account_number', 'ACC001003')->first();
        $jointAccount = Account::where('account_number', 'ACC001004')->first();
        $ahmed = User::where('email', 'ahmed@example.com')->first();

        // Create some transfers
        $transfer1 = Transfer::create([
            'from_account_id' => $currentAccount->id,
            'to_account_id' => $savingsAccount->id,
            'initiated_by_user_id' => $ahmed->id,
            'amount' => 500.00,
            'reference' => 'TRF000001',
            'description' => 'Transfer to savings',
            'status' => 'COMPLETED',
            'executed_at' => now()->subDays(5),
        ]);

        $transfer2 = Transfer::create([
            'from_account_id' => $jointAccount->id,
            'to_account_id' => $currentAccount->id,
            'initiated_by_user_id' => $ahmed->id,
            'amount' => 1000.00,
            'reference' => 'TRF000002',
            'description' => 'Joint account transfer',
            'status' => 'COMPLETED',
            'executed_at' => now()->subDays(3),
        ]);

        // Create transactions for the transfers
        // Transfer 1 transactions
        Transaction::create([
            'account_id' => $currentAccount->id,
            'type' => 'TRANSFER',
            'amount' => -500.00,
            'balance_before' => 5500.00,
            'balance_after' => 5000.00,
            'reference' => 'TXN000001',
            'description' => 'Transfer to ACC001002',
            'status' => 'COMPLETED',
            'related_transfer_id' => $transfer1->id,
        ]);

        Transaction::create([
            'account_id' => $savingsAccount->id,
            'type' => 'TRANSFER',
            'amount' => 500.00,
            'balance_before' => 14500.00,
            'balance_after' => 15000.00,
            'reference' => 'TXN000002',
            'description' => 'Transfer from ACC001001',
            'status' => 'COMPLETED',
            'related_transfer_id' => $transfer1->id,
        ]);

        // Transfer 2 transactions
        Transaction::create([
            'account_id' => $jointAccount->id,
            'type' => 'TRANSFER',
            'amount' => -1000.00,
            'balance_before' => 9000.00,
            'balance_after' => 8000.00,
            'reference' => 'TXN000003',
            'description' => 'Transfer to ACC001001',
            'status' => 'COMPLETED',
            'related_transfer_id' => $transfer2->id,
        ]);

        Transaction::create([
            'account_id' => $currentAccount->id,
            'type' => 'TRANSFER',
            'amount' => 1000.00,
            'balance_before' => 4000.00,
            'balance_after' => 5000.00,
            'reference' => 'TXN000004',
            'description' => 'Transfer from ACC001004',
            'status' => 'COMPLETED',
            'related_transfer_id' => $transfer2->id,
        ]);

        // Monthly fee transaction
        Transaction::create([
            'account_id' => $currentAccount->id,
            'type' => 'FEE',
            'amount' => -50.00,
            'balance_before' => 5050.00,
            'balance_after' => 5000.00,
            'reference' => 'TXN000005',
            'description' => 'Monthly account fee',
            'status' => 'COMPLETED',
        ]);

        // Interest transaction for savings account
        Transaction::create([
            'account_id' => $savingsAccount->id,
            'type' => 'INTEREST',
            'amount' => 43.75,
            'balance_before' => 14556.25,
            'balance_after' => 15000.00,
            'reference' => 'TXN000006',
            'description' => 'Monthly interest (3.5%)',
            'status' => 'COMPLETED',
        ]);

        // Withdrawal from minor account by guardian
        Transaction::create([
            'account_id' => $minorAccount->id,
            'type' => 'TRANSFER',
            'amount' => -200.00,
            'balance_before' => 2200.00,
            'balance_after' => 2000.00,
            'reference' => 'TXN000007',
            'description' => 'Withdrawal by guardian',
            'status' => 'COMPLETED',
        ]);

        // Update monthly withdrawal counts
        $savingsAccount->update(['monthly_withdrawals_count' => 1]);
        $minorAccount->update(['monthly_withdrawals_count' => 1]);
    }
}
