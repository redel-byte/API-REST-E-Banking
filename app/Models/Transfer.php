<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_account_id',
        'to_account_id',
        'initiated_by_user_id',
        'amount',
        'reference',
        'description',
        'status',
        'failure_reason',
        'executed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'executed_at' => 'datetime',
    ];

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by_user_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'related_transfer_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'COMPLETED';
    }

    public function isFailed(): bool
    {
        return $this->status === 'FAILED';
    }

    public function canBeExecuted(): bool
    {
        return $this->isPending() && 
               $this->fromAccount->isActive() && 
               $this->toAccount->isActive() &&
               $this->fromAccount->canWithdraw($this->amount);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transfer) {
            if (empty($transfer->reference)) {
                $transfer->reference = 'TRF' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            }
        });
    }
}
