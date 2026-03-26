<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transfer;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\AccountRepository;
use App\Repositories\TransferRepository;
use App\Repositories\TransactionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransferService
{
    protected AccountRepository $accountRepository;
    protected TransferRepository $transferRepository;
    protected TransactionRepository $transactionRepository;

    public function __construct(
        AccountRepository $accountRepository,
        TransferRepository $transferRepository,
        TransactionRepository $transactionRepository
    ) {
        $this->accountRepository = $accountRepository;
        $this->transferRepository = $transferRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function createTransfer(array $data, User $user): Transfer
    {
        return DB::transaction(function () use ($data, $user) {
            $fromAccount = $this->accountRepository->findById($data['from_account_id']);
            $toAccount = $this->accountRepository->findById($data['to_account_id']);

            $this->validateTransferRequest($fromAccount, $toAccount, $data['amount'], $user);

            $transfer = $this->transferRepository->create([
                'from_account_id' => $fromAccount->id,
                'to_account_id' => $toAccount->id,
                'initiated_by_user_id' => $user->id,
                'amount' => $data['amount'],
                'description' => $data['description'] ?? null,
                'status' => 'PENDING'
            ]);

            try {
                $this->executeTransfer($transfer, $fromAccount, $toAccount);
            } catch (\Exception $e) {
                $this->transferRepository->update($transfer, [
                    'status' => 'FAILED',
                    'failure_reason' => $e->getMessage()
                ]);
                throw $e;
            }

            return $transfer->load(['fromAccount', 'toAccount', 'initiatedBy']);
        });
    }

    public function getTransferDetails(int $transferId, User $user): Transfer
    {
        $transfer = $this->transferRepository->findById($transferId);
        
        if (!$transfer) {
            throw ValidationException::withMessages([
                'transfer' => 'Transfer not found'
            ]);
        }

        $fromAccount = $transfer->fromAccount;
        $toAccount = $transfer->toAccount;

        $canAccess = $this->canUserAccessAccount($user, $fromAccount) || 
                    $this->canUserAccessAccount($user, $toAccount) || 
                    $user->isAdmin();

        if (!$canAccess) {
            throw ValidationException::withMessages([
                'transfer' => 'Access denied'
            ]);
        }

        return $transfer->load(['fromAccount', 'toAccount', 'initiatedBy', 'transactions']);
    }

    public function getAccountTransactions(int $accountId, array $filters, User $user): array
    {
        $account = $this->accountRepository->findById($accountId);
        
        if (!$account || !$this->canUserAccessAccount($user, $account)) {
            throw ValidationException::withMessages([
                'account' => 'Account not found or access denied'
            ]);
        }

        return $this->transactionRepository->getAccountTransactions($account, $filters);
    }

    public function getTransactionDetails(int $transactionId, User $user): Transaction
    {
        $transaction = $this->transactionRepository->findById($transactionId);
        
        if (!$transaction) {
            throw ValidationException::withMessages([
                'transaction' => 'Transaction not found'
            ]);
        }

        $account = $transaction->account;
        
        if (!$this->canUserAccessAccount($user, $account)) {
            throw ValidationException::withMessages([
                'transaction' => 'Access denied'
            ]);
        }

        return $transaction->load(['account', 'relatedTransfer']);
    }

    private function validateTransferRequest(Account $fromAccount, Account $toAccount, float $amount, User $user): void
    {
        if (!$fromAccount || !$toAccount) {
            throw ValidationException::withMessages([
                'accounts' => 'One or both accounts not found'
            ]);
        }

        if ($fromAccount->id === $toAccount->id) {
            throw ValidationException::withMessages([
                'transfer' => 'Cannot transfer to the same account'
            ]);
        }

        if (!$this->canUserOperateAccount($user, $fromAccount)) {
            throw ValidationException::withMessages([
                'from_account' => 'You do not have permission to operate this account'
            ]);
        }

        if (!$fromAccount->isActive()) {
            throw ValidationException::withMessages([
                'from_account' => 'Source account is not active'
            ]);
        }

        if (!$toAccount->isActive()) {
            throw ValidationException::withMessages([
                'to_account' => 'Destination account is not active'
            ]);
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Transfer amount must be positive'
            ]);
        }

        if ($amount > 10000) {
            throw ValidationException::withMessages([
                'amount' => 'Daily transfer limit exceeded (10,000 MAD)'
            ]);
        }

        if (!$fromAccount->canWithdraw($amount)) {
            if ($fromAccount->getAvailableBalance() < $amount) {
                throw ValidationException::withMessages([
                    'amount' => 'Insufficient balance'
                ]);
            }

            if ($fromAccount->isSavings() && $fromAccount->monthly_withdrawals_count >= $fromAccount->monthly_withdrawal_limit) {
                throw ValidationException::withMessages([
                    'amount' => 'Monthly withdrawal limit exceeded for savings account'
                ]);
            }

            if ($fromAccount->isMinor() && $fromAccount->monthly_withdrawals_count >= $fromAccount->monthly_withdrawal_limit) {
                throw ValidationException::withMessages([
                    'amount' => 'Monthly withdrawal limit exceeded for minor account'
                ]);
            }
        }

        $this->checkDailyTransferLimit($user, $amount);
    }

    private function executeTransfer(Transfer $transfer, Account $fromAccount, Account $toAccount): void
    {
        $amount = $transfer->amount;

        $fromBalanceBefore = $fromAccount->balance;
        $toBalanceBefore = $toAccount->balance;

        $fromAccount->balance -= $amount;
        $toAccount->balance += $amount;

        if ($fromAccount->isSavings() || $fromAccount->isMinor()) {
            $fromAccount->incrementMonthlyWithdrawals();
        }

        $this->accountRepository->update($fromAccount, ['balance' => $fromAccount->balance]);
        $this->accountRepository->update($toAccount, ['balance' => $toAccount->balance]);

        $this->transactionRepository->create([
            'account_id' => $fromAccount->id,
            'type' => 'TRANSFER',
            'amount' => -$amount,
            'balance_before' => $fromBalanceBefore,
            'balance_after' => $fromAccount->balance,
            'description' => "Transfer to {$toAccount->account_number}",
            'status' => 'COMPLETED',
            'related_transfer_id' => $transfer->id
        ]);

        $this->transactionRepository->create([
            'account_id' => $toAccount->id,
            'type' => 'TRANSFER',
            'amount' => $amount,
            'balance_before' => $toBalanceBefore,
            'balance_after' => $toAccount->balance,
            'description' => "Transfer from {$fromAccount->account_number}",
            'status' => 'COMPLETED',
            'related_transfer_id' => $transfer->id
        ]);

        $this->transferRepository->update($transfer, [
            'status' => 'COMPLETED',
            'executed_at' => now()
        ]);
    }

    private function checkDailyTransferLimit(User $user, float $amount): void
    {
        $todayTotal = $this->transferRepository->getUserDailyTotal($user);
        
        if (($todayTotal + $amount) > 10000) {
            throw ValidationException::withMessages([
                'amount' => 'Daily transfer limit would be exceeded (10,000 MAD)'
            ]);
        }
    }

    private function canUserAccessAccount(User $user, Account $account): bool
    {
        return $account->users()->where('user_id', $user->id)->exists() || $user->isAdmin();
    }

    private function canUserOperateAccount(User $user, Account $account): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $account->canUserOperate($user);
    }
}
