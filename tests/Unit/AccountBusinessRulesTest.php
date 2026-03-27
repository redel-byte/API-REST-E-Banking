<?php

namespace Tests\Unit;

use App\Models\Account;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountBusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    protected $adultUser;
    protected $minorUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adultUser = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'birth_date' => '1990-01-01',
            'role' => 'client',
        ]);

        $this->minorUser = User::create([
            'first_name' => 'Minor',
            'last_name' => 'User',
            'email' => 'minor@example.com',
            'password' => bcrypt('password'),
            'birth_date' => '2010-01-01',
            'role' => 'client',
        ]);
    }

    public function test_current_account_can_withdraw_with_overdraft()
    {
        $account = Account::create([
            'account_number' => 'ACC001',
            'type' => 'COURANT',
            'status' => 'ACTIVE',
            'balance' => 100.00,
            'overdraft_limit' => 500.00,
        ]);

        $this->assertTrue($account->canWithdraw(400.00));
        $this->assertTrue($account->canWithdraw(600.00)); // Uses overdraft
        $this->assertFalse($account->canWithdraw(700.00)); // Exceeds overdraft
    }

    public function test_savings_account_cannot_withdraw_beyond_limit()
    {
        $account = Account::create([
            'account_number' => 'ACC002',
            'type' => 'EPARGNE',
            'status' => 'ACTIVE',
            'balance' => 1000.00,
            'monthly_withdrawal_limit' => 3,
            'monthly_withdrawals_count' => 3,
        ]);

        $this->assertFalse($account->canWithdraw(100.00)); // Limit reached
        $this->assertFalse($account->canWithdraw(1500.00)); // Insufficient balance
    }

    public function test_minor_account_cannot_withdraw_beyond_limit()
    {
        $account = Account::create([
            'account_number' => 'ACC003',
            'type' => 'MINEUR',
            'status' => 'ACTIVE',
            'balance' => 500.00,
            'monthly_withdrawal_limit' => 2,
            'monthly_withdrawals_count' => 2,
        ]);

        $this->assertFalse($account->canWithdraw(100.00)); // Limit reached
    }

    public function test_blocked_account_cannot_withdraw()
    {
        $account = Account::create([
            'account_number' => 'ACC004',
            'type' => 'COURANT',
            'status' => 'BLOCKED',
            'balance' => 1000.00,
            'overdraft_limit' => 500.00,
        ]);

        $this->assertFalse($account->canWithdraw(100.00));
    }

    public function test_closed_account_cannot_withdraw()
    {
        $account = Account::create([
            'account_number' => 'ACC005',
            'type' => 'COURANT',
            'status' => 'CLOSED',
            'balance' => 1000.00,
            'overdraft_limit' => 500.00,
        ]);

        $this->assertFalse($account->canWithdraw(100.00));
    }

    public function test_savings_account_has_no_overdraft()
    {
        $account = Account::create([
            'account_number' => 'ACC006',
            'type' => 'EPARGNE',
            'status' => 'ACTIVE',
            'balance' => 100.00,
            'overdraft_limit' => 0.00,
        ]);

        $this->assertFalse($account->hasOverdraft());
        $this->assertEquals(100.00, $account->getAvailableBalance());
    }

    public function test_minor_account_has_no_overdraft()
    {
        $account = Account::create([
            'account_number' => 'ACC007',
            'type' => 'MINEUR',
            'status' => 'ACTIVE',
            'balance' => 100.00,
            'overdraft_limit' => 0.00,
        ]);

        $this->assertFalse($account->hasOverdraft());
        $this->assertEquals(100.00, $account->getAvailableBalance());
    }

    public function test_user_is_minor_based_on_birth_date()
    {
        $this->assertTrue($this->minorUser->isMinor());
        $this->assertFalse($this->adultUser->isMinor());
    }

    public function test_user_is_admin()
    {
        $adminUser = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'birth_date' => '1980-01-01',
            'role' => 'admin',
        ]);

        $this->assertTrue($adminUser->isAdmin());
        $this->assertFalse($this->adultUser->isAdmin());
    }

    public function test_account_type_identification()
    {
        $currentAccount = Account::create([
            'account_number' => 'ACC008',
            'type' => 'COURANT',
            'status' => 'ACTIVE',
            'balance' => 0,
        ]);

        $savingsAccount = Account::create([
            'account_number' => 'ACC009',
            'type' => 'EPARGNE',
            'status' => 'ACTIVE',
            'balance' => 0,
        ]);

        $minorAccount = Account::create([
            'account_number' => 'ACC010',
            'type' => 'MINEUR',
            'status' => 'ACTIVE',
            'balance' => 0,
        ]);

        $this->assertTrue($currentAccount->isCurrent());
        $this->assertFalse($currentAccount->isSavings());
        $this->assertFalse($currentAccount->isMinor());

        $this->assertFalse($savingsAccount->isCurrent());
        $this->assertTrue($savingsAccount->isSavings());
        $this->assertFalse($savingsAccount->isMinor());

        $this->assertFalse($minorAccount->isCurrent());
        $this->assertFalse($minorAccount->isSavings());
        $this->assertTrue($minorAccount->isMinor());
    }

    public function test_account_status_identification()
    {
        $activeAccount = Account::create([
            'account_number' => 'ACC011',
            'type' => 'COURANT',
            'status' => 'ACTIVE',
            'balance' => 0,
        ]);

        $blockedAccount = Account::create([
            'account_number' => 'ACC012',
            'type' => 'COURANT',
            'status' => 'BLOCKED',
            'balance' => 0,
        ]);

        $closedAccount = Account::create([
            'account_number' => 'ACC013',
            'type' => 'COURANT',
            'status' => 'CLOSED',
            'balance' => 0,
        ]);

        $this->assertTrue($activeAccount->isActive());
        $this->assertFalse($activeAccount->isBlocked());
        $this->assertFalse($activeAccount->isClosed());

        $this->assertFalse($blockedAccount->isActive());
        $this->assertTrue($blockedAccount->isBlocked());
        $this->assertFalse($blockedAccount->isClosed());

        $this->assertFalse($closedAccount->isActive());
        $this->assertFalse($closedAccount->isBlocked());
        $this->assertTrue($closedAccount->isClosed());
    }
}
