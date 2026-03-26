<?php

namespace App\Repositories;

use App\Models\Account;
use App\Models\Transaction;

class TransactionRepository
{
    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    public function update(Transaction $transaction, array $data): Transaction
    {
        $transaction->update($data);
        return $transaction;
    }

    public function findById(int $id): ?Transaction
    {
        return Transaction::find($id);
    }

    public function findByReference(string $reference): ?Transaction
    {
        return Transaction::where('reference', $reference)->first();
    }

    public function getAccountTransactions(Account $account, array $filters = []): array
    {
        $query = $account->transactions();

        if (isset($filters['type']) && !empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['date_from']) && !empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && !empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->with(['relatedTransfer'])
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->toArray();
    }

    public function getTransactionsByType(string $type): array
    {
        return Transaction::where('type', $type)
            ->with(['account', 'relatedTransfer'])
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->toArray();
    }

    public function getFailedTransactions(): array
    {
        return Transaction::where('status', 'FAILED')
            ->with(['account', 'relatedTransfer'])
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->toArray();
    }

    public function getPendingTransactions(): array
    {
        return Transaction::where('status', 'PENDING')
            ->with(['account', 'relatedTransfer'])
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->toArray();
    }

    public function getTransactionsByAccountAndType(int $accountId, string $type): array
    {
        return Transaction::where('account_id', $accountId)
            ->where('type', $type)
            ->with(['relatedTransfer'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->toArray();
    }

    public function getMonthlyTransactions(int $accountId, int $year, int $month): array
    {
        return Transaction::where('account_id', $accountId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->with(['relatedTransfer'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function getTotalByTypeAndAccount(int $accountId, string $type, string $dateRange = null): float
    {
        $query = Transaction::where('account_id', $accountId)
            ->where('type', $type)
            ->where('status', 'COMPLETED');

        if ($dateRange) {
            switch ($dateRange) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                    break;
                case 'year':
                    $query->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]);
                    break;
            }
        }

        return $query->sum('amount');
    }
}
