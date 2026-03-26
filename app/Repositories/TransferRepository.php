<?php

namespace App\Repositories;

use App\Models\Transfer;

class TransferRepository
{
    public function create(array $data): Transfer
    {
        return Transfer::create($data);
    }

    public function update(Transfer $transfer, array $data): Transfer
    {
        $transfer->update($data);
        return $transfer;
    }

    public function findById(int $id): ?Transfer
    {
        return Transfer::find($id);
    }

    public function findByReference(string $reference): ?Transfer
    {
        return Transfer::where('reference', $reference)->first();
    }

    public function getUserDailyTotal(\App\Models\User $user): float
    {
        return Transfer::where('initiated_by_user_id', $user->id)
            ->whereDate('created_at', today())
            ->where('status', 'COMPLETED')
            ->sum('amount');
    }

    public function getPendingTransfers(): array
    {
        return Transfer::where('status', 'PENDING')
            ->with(['fromAccount', 'toAccount', 'initiatedBy'])
            ->get()
            ->toArray();
    }

    public function getCompletedTransfers(): array
    {
        return Transfer::where('status', 'COMPLETED')
            ->with(['fromAccount', 'toAccount', 'initiatedBy'])
            ->orderBy('executed_at', 'desc')
            ->limit(100)
            ->get()
            ->toArray();
    }

    public function getFailedTransfers(): array
    {
        return Transfer::where('status', 'FAILED')
            ->with(['fromAccount', 'toAccount', 'initiatedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->toArray();
    }

    public function getAccountTransfers(int $accountId): array
    {
        return Transfer::where(function ($query) use ($accountId) {
                $query->where('from_account_id', $accountId)
                      ->orWhere('to_account_id', $accountId);
            })
            ->with(['fromAccount', 'toAccount', 'initiatedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->toArray();
    }

    public function getUserTransfers(int $userId): array
    {
        return Transfer::where('initiated_by_user_id', $userId)
            ->with(['fromAccount', 'toAccount'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->toArray();
    }
}
