<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'reference',
        'description',
        'status',
        'failure_reason',
        'related_transfer_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function relatedTransfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class, 'related_transfer_id');
    }

    public function isCompleted(): bool
    {
        return $this->status === 'COMPLETED';
    }

    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    public function isFailed(): bool
    {
        return $this->status === 'FAILED';
    }

    public function isTransfer(): bool
    {
        return $this->type === 'TRANSFER';
    }

    public function isFee(): bool
    {
        return in_array($this->type, ['FEE', 'FEE_FAILED']);
    }

    public function isInterest(): bool
    {
        return $this->type === 'INTEREST';
    }

    public function isCredit(): bool
    {
        return $this->amount > 0;
    }

    public function isDebit(): bool
    {
        return $this->amount < 0;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->reference)) {
                $transaction->reference = 'TXN' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            }
        });
    }
}
