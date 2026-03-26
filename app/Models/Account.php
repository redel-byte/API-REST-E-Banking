<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_number',
        'type',
        'status',
        'balance',
        'overdraft_limit',
        'interest_rate',
        'monthly_withdrawal_limit',
        'monthly_withdrawals_count',
        'monthly_withdrawal_reset',
        'monthly_fee',
        'block_reason',
        'blocked_at',
        'closed_at',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'overdraft_limit' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'monthly_withdrawal_limit' => 'integer',
        'monthly_withdrawals_count' => 'integer',
        'monthly_withdrawal_reset' => 'date',
        'monthly_fee' => 'decimal:2',
        'blocked_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'account_user')
            ->withPivot('role', 'accepted_closure')
            ->withTimestamps();
    }

    public function owners(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'owner');
    }

    public function guardians(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'guardian');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'from_account_id');
    }

    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'to_account_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'ACTIVE';
    }

    public function isBlocked(): bool
    {
        return $this->status === 'BLOCKED';
    }

    public function isClosed(): bool
    {
        return $this->status === 'CLOSED';
    }

    public function isCurrent(): bool
    {
        return $this->type === 'COURANT';
    }

    public function isSavings(): bool
    {
        return $this->type === 'EPARGNE';
    }

    public function isMinor(): bool
    {
        return $this->type === 'MINEUR';
    }

    public function hasOverdraft(): bool
    {
        return $this->isCurrent() && $this->overdraft_limit > 0;
    }

    public function getAvailableBalance(): float
    {
        if ($this->hasOverdraft()) {
            return (float) $this->balance + (float) $this->overdraft_limit;
        }
        return (float) $this->balance;
    }

    public function canWithdraw(float $amount): bool
    {
        if (!$this->isActive()) {
            return false;
        }

        if ($this->getAvailableBalance() < $amount) {
            return false;
        }

        if ($this->isSavings() && $this->monthly_withdrawals_count >= $this->monthly_withdrawal_limit) {
            return false;
        }

        if ($this->isMinor() && $this->monthly_withdrawals_count >= $this->monthly_withdrawal_limit) {
            return false;
        }

        return true;
    }

    public function isUserOwner(User $user): bool
    {
        return $this->owners()->where('user_id', $user->id)->exists();
    }

    public function isUserGuardian(User $user): bool
    {
        return $this->guardians()->where('user_id', $user->id)->exists();
    }

    public function canUserOperate(User $user): bool
    {
        if ($this->isMinor()) {
            return $this->isUserGuardian($user);
        }

        return $this->isUserOwner($user);
    }

    public function allOwnersAcceptedClosure(): bool
    {
        return $this->owners()->where('accepted_closure', false)->count() === 0;
    }

    public function resetMonthlyWithdrawals(): void
    {
        $this->monthly_withdrawals_count = 0;
        $this->monthly_withdrawal_reset = now()->startOfMonth();
        $this->save();
    }

    public function incrementMonthlyWithdrawals(): void
    {
        $this->monthly_withdrawals_count++;
        $this->save();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($account) {
            if (empty($account->account_number)) {
                $account->account_number = 'ACC' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            }
        });

        static::creating(function ($account) {
            switch ($account->type) {
                case 'EPARGNE':
                    $account->monthly_withdrawal_limit = 3;
                    $account->overdraft_limit = 0;
                    break;
                case 'MINEUR':
                    $account->monthly_withdrawal_limit = 2;
                    $account->overdraft_limit = 0;
                    break;
                case 'COURANT':
                    $account->monthly_withdrawal_limit = 0;
                    break;
            }
        });
    }
}
