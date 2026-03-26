<?php

namespace App\Services;

use App\Models\Account;
use App\Models\User;
use App\Repositories\AccountRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountService
{
    protected AccountRepository $accountRepository;
    protected UserRepository $userRepository;

    public function __construct(AccountRepository $accountRepository, UserRepository $userRepository)
    {
        $this->accountRepository = $accountRepository;
        $this->userRepository = $userRepository;
    }

    public function getUserAccounts(User $user): array
    {
        return $this->accountRepository->getUserAccounts($user);
    }

    public function createAccount(array $data, User $user): Account
    {
        return DB::transaction(function () use ($data, $user) {
            $accountData = $this->prepareAccountData($data);
            $account = $this->accountRepository->create($accountData);

            $this->accountRepository->attachUser($account, $user, 'owner');

            if ($account->isMinor() && isset($data['guardian_id'])) {
                $guardian = $this->userRepository->findById($data['guardian_id']);
                if (!$guardian || $guardian->isMinor()) {
                    throw ValidationException::withMessages([
                        'guardian_id' => 'Guardian must be an adult user'
                    ]);
                }
                $this->accountRepository->attachUser($account, $guardian, 'guardian');
            }

            if (isset($data['initial_deposit']) && $data['initial_deposit'] > 0) {
                $this->processInitialDeposit($account, $data['initial_deposit']);
            }

            return $account->load(['users', 'transactions']);
        });
    }

    public function getAccountDetails(int $accountId, User $user): Account
    {
        $account = $this->accountRepository->findById($accountId);
        
        if (!$account || !$this->canUserAccessAccount($user, $account)) {
            throw ValidationException::withMessages([
                'account' => 'Account not found or access denied'
            ]);
        }

        return $account->load(['users', 'transactions' => function ($query) {
            $query->latest()->limit(50);
        }]);
    }

    public function addCoOwner(int $accountId, array $data, User $user): void
    {
        DB::transaction(function () use ($accountId, $data, $user) {
            $account = $this->accountRepository->findById($accountId);
            
            if (!$account || !$this->canUserOperateAccount($user, $account)) {
                throw ValidationException::withMessages([
                    'account' => 'Account not found or operation not permitted'
                ]);
            }

            if ($account->isMinor()) {
                throw ValidationException::withMessages([
                    'account' => 'Cannot add co-owners to minor accounts'
                ]);
            }

            if (!$account->isActive()) {
                throw ValidationException::withMessages([
                    'account' => 'Cannot add co-owners to inactive accounts'
                ]);
            }

            $coOwner = $this->userRepository->findById($data['user_id']);
            if (!$coOwner) {
                throw ValidationException::withMessages([
                    'user_id' => 'User not found'
                ]);
            }

            if ($account->users()->where('user_id', $coOwner->id)->exists()) {
                throw ValidationException::withMessages([
                    'user_id' => 'User is already associated with this account'
                ]);
            }

            $this->accountRepository->attachUser($account, $coOwner, 'owner');
        });
    }

    public function removeCoOwner(int $accountId, int $userId, User $user): void
    {
        DB::transaction(function () use ($accountId, $userId, $user) {
            $account = $this->accountRepository->findById($accountId);
            
            if (!$account || !$this->canUserOperateAccount($user, $account)) {
                throw ValidationException::withMessages([
                    'account' => 'Account not found or operation not permitted'
                ]);
            }

            $coOwner = $account->users()->where('user_id', $userId)->first();
            if (!$coOwner) {
                throw ValidationException::withMessages([
                    'user_id' => 'User is not a co-owner of this account'
                ]);
            }

            if ($account->owners()->count() <= 1) {
                throw ValidationException::withMessages([
                    'account' => 'Cannot remove the last owner of the account'
                ]);
            }

            $this->accountRepository->detachUser($account, $userId);
        });
    }

    public function assignGuardian(int $accountId, array $data, User $user): void
    {
        DB::transaction(function () use ($accountId, $data, $user) {
            $account = $this->accountRepository->findById($accountId);
            
            if (!$account || !$this->canUserOperateAccount($user, $account)) {
                throw ValidationException::withMessages([
                    'account' => 'Account not found or operation not permitted'
                ]);
            }

            if (!$account->isMinor()) {
                throw ValidationException::withMessages([
                    'account' => 'Guardians can only be assigned to minor accounts'
                ]);
            }

            $guardian = $this->userRepository->findById($data['guardian_id']);
            if (!$guardian || $guardian->isMinor()) {
                throw ValidationException::withMessages([
                    'guardian_id' => 'Guardian must be an adult user'
                ]);
            }

            $this->accountRepository->attachUser($account, $guardian, 'guardian');
        });
    }

    public function convertMinorAccount(int $accountId, User $user): void
    {
        DB::transaction(function () use ($accountId, $user) {
            $account = $this->accountRepository->findById($accountId);
            
            if (!$account || !$this->canUserOperateAccount($user, $account)) {
                throw ValidationException::withMessages([
                    'account' => 'Account not found or operation not permitted'
                ]);
            }

            if (!$account->isMinor()) {
                throw ValidationException::withMessages([
                    'account' => 'Only minor accounts can be converted'
                ]);
            }

            $minorUser = $account->owners()->first();
            if (!$minorUser || $minorUser->isMinor()) {
                throw ValidationException::withMessages([
                    'account' => 'Account owner must be 18 years or older to convert'
                ]);
            }

            $guardian = $account->guardians()->first();
            if ($guardian) {
                $this->accountRepository->detachUser($account, $guardian->id);
            }

            $this->accountRepository->update($account, [
                'type' => 'COURANT',
                'monthly_withdrawal_limit' => 0,
            ]);
        });
    }

    public function requestAccountClosure(int $accountId, User $user): array
    {
        return DB::transaction(function () use ($accountId, $user) {
            $account = $this->accountRepository->findById($accountId);
            
            if (!$account || !$this->canUserOperateAccount($user, $account)) {
                throw ValidationException::withMessages([
                    'account' => 'Account not found or operation not permitted'
                ]);
            }

            if ($account->balance != 0) {
                throw ValidationException::withMessages([
                    'account' => 'Account balance must be zero to request closure'
                ]);
            }

            if ($account->owners()->count() > 1) {
                $this->accountRepository->updatePivot($account, $user->id, [
                    'accepted_closure' => true
                ]);

                if ($account->allOwnersAcceptedClosure()) {
                    $this->accountRepository->update($account, [
                        'status' => 'CLOSED',
                        'closed_at' => now()
                    ]);
                    return ['message' => 'Account closed successfully'];
                }

                return ['message' => 'Closure request recorded. Waiting for other owners to confirm.'];
            }

            $this->accountRepository->update($account, [
                'status' => 'CLOSED',
                'closed_at' => now()
            ]);

            return ['message' => 'Account closed successfully'];
        });
    }

    private function prepareAccountData(array $data): array
    {
        $accountData = [
            'type' => $data['type'],
            'status' => 'ACTIVE',
            'balance' => 0,
        ];

        if (isset($data['overdraft_limit'])) {
            $accountData['overdraft_limit'] = $data['overdraft_limit'];
        }

        if (isset($data['interest_rate'])) {
            $accountData['interest_rate'] = $data['interest_rate'];
        }

        return $accountData;
    }

    private function processInitialDeposit(Account $account, float $amount): void
    {
        $this->accountRepository->update($account, ['balance' => $amount]);

        $this->accountRepository->createTransaction($account, [
            'type' => 'TRANSFER',
            'amount' => $amount,
            'balance_before' => 0,
            'balance_after' => $amount,
            'description' => 'Initial deposit',
            'status' => 'COMPLETED'
        ]);
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
