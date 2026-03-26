<?php

namespace App\Repositories;

use App\Models\Account;
use App\Models\User;
use App\Models\Transaction;

class AccountRepository
{
    public function create(array $data): Account
    {
        return Account::create($data);
    }

    public function update(Account $account, array $data): Account
    {
        $account->update($data);
        return $account;
    }

    public function findById(int $id): ?Account
    {
        return Account::find($id);
    }

    public function findByAccountNumber(string $accountNumber): ?Account
    {
        return Account::where('account_number', $accountNumber)->first();
    }

    public function getUserAccounts(User $user): array
    {
        return $user->accounts()
            ->with(['users', 'transactions' => function ($query) {
                $query->latest()->limit(10);
            }])
            ->get()
            ->toArray();
    }

    public function allWithRelations(): array
    {
        return Account::with(['users', 'transactions' => function ($query) {
            $query->latest()->limit(20);
        }])->get()->toArray();
    }

    public function getCurrentAccounts(): array
    {
        return Account::where('type', 'COURANT')
            ->where('status', 'ACTIVE')
            ->where('monthly_fee', '>', 0)
            ->get()
            ->toArray();
    }

    public function getSavingsAndMinorAccounts(): array
    {
        return Account::whereIn('type', ['EPARGNE', 'MINEUR'])
            ->where('status', 'ACTIVE')
            ->where('interest_rate', '>', 0)
            ->where('balance', '>', 0)
            ->get()
            ->toArray();
    }

    public function attachUser(Account $account, User $user, string $role): void
    {
        $account->users()->attach($user->id, ['role' => $role]);
    }

    public function detachUser(Account $account, int $userId): void
    {
        $account->users()->detach($userId);
    }

    public function updatePivot(Account $account, int $userId, array $data): void
    {
        $account->users()->updateExistingPivot($userId, $data);
    }

    public function createTransaction(Account $account, array $data): Transaction
    {
        return $account->transactions()->create($data);
    }

    public function resetMonthlyWithdrawals(): void
    {
        Account::whereIn('type', ['EPARGNE', 'MINEUR'])
            ->where('monthly_withdrawal_reset', '<', now()->startOfMonth())
            ->update([
                'monthly_withdrawals_count' => 0,
                'monthly_withdrawal_reset' => now()->startOfMonth()
            ]);
    }
}
