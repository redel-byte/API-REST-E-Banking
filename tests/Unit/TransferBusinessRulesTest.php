<?php

namespace Tests\Unit;

use App\Models\Account;
use App\Models\Transfer;
use App\Models\User;
use App\Services\TransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Validation\ValidationException;

class TransferBusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    protected $adultUser;
    protected $transferService;

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

        $this->transferService = app(TransferService::class);
    }

    public function test_cannot_transfer_to_same_account()
    {
        $account = Account::create([
            'account_number' => 'ACC001',
            'type' => 'COURANT',
            'status' => 'ACTIVE',
            'balance' => 1000.00,
            'overdraft_limit' => 500.00,
        ]);
        
        $account->users()->attach($this->adultUser->id, ['role' => 'owner']);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Cannot transfer to the same account');

        $this->transferService->createTransfer([
            'from_account_id' => $account->id,
            'to_account_id' => $account->id,
            'amount' => 100.00,
        ], $this->adultUser);
    }

    public function test_cannot_transfer_with_insufficient_balance()
    {
        $fromAccount = Account::create([
            'account_number' => 'ACC002',
            'type' => 'COURANT',
            'status' => 'ACTIVE',
            'balance' => 100.00,
            'overdraft_limit' => 0.00,
        ]);
        
        $fromAccount->users()->attach($this->adultUser->id, ['role' => 'owner']);

        $toAccount = Account::create([
            'account_number' => 'ACC003',
            'type' => 'COURANT',
            'status' => 'ACTIVE',
            'balance' => 0.00,
            'overdraft_limit' => 0.00,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Insufficient balance');

        $this->transferService->createTransfer([
            'from_account_id' => $fromAccount->id,
            'to_account_id' => $toAccount->id,
            'amount' => 200.00,
        ], $this->adultUser);
    }

    public function test_cannot_transfer_from_blocked_account()
    {
        $fromAccount = Account::create([
            'account_number' => 'ACC004',
            'type' => 'COURANT',
            'status' => 'BLOCKED',
            'balance' => 1000.00,
            'overdraft_limit' => 0.00,
        ]);
        
        $fromAccount->users()->attach($this->adultUser->id, ['role' => 'owner']);

        $toAccount = Account::create([
            'account_number' => 'ACC005',
            'type' => 'COURANT',
            'status' => 'ACTIVE',
            'balance' => 0.00,
            'overdraft_limit' => 0.00,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Source account is not active');

        $this->transferService->createTransfer([
            'from_account_id' => $fromAccount->id,
            'to_account_id' => $toAccount->id,
            'amount' => 100.00,
        ], $this->adultUser);
    }

    public function test_cannot_transfer_to_blocked_account()
    {
        $fromAccount = Account::create([
            'account_number' => 'ACC006',
            'type' => 'COURANT',
            'status' => 'ACTIVE',
            'balance' => 1000.00,
            'overdraft_limit' => 0.00,
        ]);
        
        $fromAccount->users()->attach($this->adultUser->id, ['role' => 'owner']);

        $toAccount = Account::create([
            'account_number' => 'ACC007',
            'type' => 'COURANT',
            'status' => 'BLOCKED',
            'balance' => 0.00,
            'overdraft_limit' => 0.00,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Destination account is not active');

        $this->transferService->createTransfer([
            'from_account_id' => $fromAccount->id,
            'to_account_id' => $toAccount->id,
            'amount' => 100.00,
        ], $this->adultUser);
    }

    public function test_cannot_transfer_exceeding_daily_limit()
    {
        $fromAccount = Account::create([
            'account_number' => 'ACC008',
            'type' => 'COURANT',
            'status' => 'ACTIVE',
            'balance' => 20000.00,
            'overdraft_limit' => 0.00,
        ]);
        
        $fromAccount->users()->attach($this->adultUser->id, ['role' => 'owner']);

        $toAccount = Account::create([
            'account_number' => 'ACC009',
            'type' => 'COURANT',
            'status' => 'ACTIVE',
            'balance' => 0.00,
            'overdraft_limit' => 0.00,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Daily transfer limit exceeded');

        $this->transferService->createTransfer([
            'from_account_id' => $fromAccount->id,
            'to_account_id' => $toAccount->id,
            'amount' => 15000.00,
        ], $this->adultUser);
    }

    public function test_cannot_transfer_negative_amount()
    {
        $fromAccount = Account::create([
            'account_number' => 'ACC010',
            'type' => 'COURANT',
            'status' => 'ACTIVE',
            'balance' => 1000.00,
            'overdraft_limit' => 0.00,
        ]);
        
        $fromAccount->users()->attach($this->adultUser->id, ['role' => 'owner']);

        $toAccount = Account::create([
            'account_number' => 'ACC011',
            'type' => 'COURANT',
            'status' => 'ACTIVE',
            'balance' => 0.00,
            'overdraft_limit' => 0.00,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Transfer amount must be positive');

        $this->transferService->createTransfer([
            'from_account_id' => $fromAccount->id,
            'to_account_id' => $toAccount->id,
            'amount' => -100.00,
        ], $this->adultUser);
    }

    public function test_successful_transfer_updates_balances()
    {
        $fromAccount = Account::create([
            'account_number' => 'ACC012',
            'type' => 'COURANT',
            'status' => 'ACTIVE',
            'balance' => 1000.00,
            'overdraft_limit' => 0.00,
        ]);
        
        $fromAccount->users()->attach($this->adultUser->id, ['role' => 'owner']);

        $toAccount = Account::create([
            'account_number' => 'ACC013',
            'type' => 'COURANT',
            'status' => 'ACTIVE',
            'balance' => 500.00,
            'overdraft_limit' => 0.00,
        ]);

        $transfer = $this->transferService->createTransfer([
            'from_account_id' => $fromAccount->id,
            'to_account_id' => $toAccount->id,
            'amount' => 300.00,
            'description' => 'Test transfer',
        ], $this->adultUser);

        $this->assertEquals('COMPLETED', $transfer->status);
        $this->assertEquals(700.00, $fromAccount->fresh()->balance);
        $this->assertEquals(800.00, $toAccount->fresh()->balance);
    }

    public function test_transfer_creates_transactions()
    {
        $fromAccount = Account::create([
            'account_number' => 'ACC014',
            'type' => 'COURANT',
            'status' => 'ACTIVE',
            'balance' => 1000.00,
            'overdraft_limit' => 0.00,
        ]);
        
        $fromAccount->users()->attach($this->adultUser->id, ['role' => 'owner']);

        $toAccount = Account::create([
            'account_number' => 'ACC015',
            'type' => 'COURANT',
            'status' => 'ACTIVE',
            'balance' => 0.00,
            'overdraft_limit' => 0.00,
        ]);

        $transfer = $this->transferService->createTransfer([
            'from_account_id' => $fromAccount->id,
            'to_account_id' => $toAccount->id,
            'amount' => 200.00,
        ], $this->adultUser);

        $this->assertEquals(2, $transfer->transactions()->count());
        
        $fromTransaction = $transfer->transactions()->where('account_id', $fromAccount->id)->first();
        $toTransaction = $transfer->transactions()->where('account_id', $toAccount->id)->first();

        $this->assertEquals(-200.00, $fromTransaction->amount);
        $this->assertEquals(200.00, $toTransaction->amount);
        $this->assertEquals('COMPLETED', $fromTransaction->status);
        $this->assertEquals('COMPLETED', $toTransaction->status);
    }
}
