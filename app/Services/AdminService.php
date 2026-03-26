<?php

namespace App\Services;

use App\Models\Account;
use App\Models\User;
use App\Repositories\AccountRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminService
{
    protected AccountRepository $accountRepository;

    public function __construct(AccountRepository $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    public function getAllAccounts(): array
    {
        return $this->accountRepository->allWithRelations();
    }

    public function blockAccount(int $accountId, string $reason): void
    {
        DB::transaction(function () use ($accountId, $reason) {
            $account = $this->accountRepository->findById($accountId);
            
            if (!$account) {
                throw ValidationException::withMessages([
                    'account' => 'Account not found'
                ]);
            }

            if ($account->isBlocked()) {
                throw ValidationException::withMessages([
                    'account' => 'Account is already blocked'
                ]);
            }

            if ($account->isClosed()) {
                throw ValidationException::withMessages([
                    'account' => 'Cannot block a closed account'
                ]);
            }

            $this->accountRepository->update($account, [
                'status' => 'BLOCKED',
                'block_reason' => $reason,
                'blocked_at' => now()
            ]);
        });
    }

    public function unblockAccount(int $accountId): void
    {
        DB::transaction(function () use ($accountId) {
            $account = $this->accountRepository->findById($accountId);
            
            if (!$account) {
                throw ValidationException::withMessages([
                    'account' => 'Account not found'
                ]);
            }

            if (!$account->isBlocked()) {
                throw ValidationException::withMessages([
                    'account' => 'Account is not blocked'
                ]);
            }

            if ($account->isClosed()) {
                throw ValidationException::withMessages([
                    'account' => 'Cannot unblock a closed account'
                ]);
            }

            $this->accountRepository->update($account, [
                'status' => 'ACTIVE',
                'block_reason' => null,
                'blocked_at' => null
            ]);
        });
    }

    public function closeAccount(int $accountId): void
    {
        DB::transaction(function () use ($accountId) {
            $account = $this->accountRepository->findById($accountId);
            
            if (!$account) {
                throw ValidationException::withMessages([
                    'account' => 'Account not found'
                ]);
            }

            if ($account->isClosed()) {
                throw ValidationException::withMessages([
                    'account' => 'Account is already closed'
                ]);
            }

            if ($account->balance != 0) {
                throw ValidationException::withMessages([
                    'account' => 'Cannot close account with non-zero balance'
                ]);
            }

            $this->accountRepository->update($account, [
                'status' => 'CLOSED',
                'closed_at' => now()
            ]);
        });
    }

    public function applyMonthlyFees(): void
    {
        DB::transaction(function () {
            $currentAccounts = $this->accountRepository->getCurrentAccounts();
            
            foreach ($currentAccounts as $account) {
                if ($account->isActive() && $account->monthly_fee > 0) {
                    $this->applyFeeToAccount($account);
                }
            }
        });
    }

    public function applyMonthlyInterest(): void
    {
        DB::transaction(function () {
            $savingsAndMinorAccounts = $this->accountRepository->getSavingsAndMinorAccounts();
            
            foreach ($savingsAndMinorAccounts as $account) {
                if ($account->isActive() && $account->interest_rate > 0 && $account->balance > 0) {
                    $this->applyInterestToAccount($account);
                }
            }
        });
    }

    private function applyFeeToAccount(Account $account): void
    {
        $feeAmount = $account->monthly_fee;
        $balanceBefore = $account->balance;

        if ($account->balance >= $feeAmount) {
            $account->balance -= $feeAmount;
            $this->accountRepository->update($account, ['balance' => $account->balance]);

            $this->accountRepository->createTransaction($account, [
                'type' => 'FEE',
                'amount' => -$feeAmount,
                'balance_before' => $balanceBefore,
                'balance_after' => $account->balance,
                'description' => 'Monthly account fee',
                'status' => 'COMPLETED'
            ]);
        } else {
            $this->accountRepository->update($account, [
                'status' => 'BLOCKED',
                'block_reason' => 'Insufficient balance for monthly fee',
                'blocked_at' => now()
            ]);

            $this->accountRepository->createTransaction($account, [
                'type' => 'FEE_FAILED',
                'amount' => 0,
                'balance_before' => $balanceBefore,
                'balance_after' => $account->balance,
                'description' => 'Failed monthly fee - insufficient balance',
                'status' => 'COMPLETED'
            ]);
        }
    }

    private function applyInterestToAccount(Account $account): void
    {
        $monthlyInterestRate = $account->interest_rate / 12 / 100;
        $interestAmount = $account->balance * $monthlyInterestRate;
        $balanceBefore = $account->balance;

        $account->balance += $interestAmount;
        $this->accountRepository->update($account, ['balance' => $account->balance]);

        $this->accountRepository->createTransaction($account, [
            'type' => 'INTEREST',
            'amount' => $interestAmount,
            'balance_before' => $balanceBefore,
            'balance_after' => $account->balance,
            'description' => sprintf('Monthly interest (%.2f%%)', $account->interest_rate),
            'status' => 'COMPLETED'
        ]);
    }
}
