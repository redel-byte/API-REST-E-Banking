<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user;
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function getAllAdmins(): array
    {
        return User::where('role', 'admin')->get()->toArray();
    }

    public function getAllClients(): array
    {
        return User::where('role', 'client')->get()->toArray();
    }

    public function getMinorUsers(): array
    {
        return User::where('birth_date', '>', now()->subYears(18))
            ->where('role', 'client')
            ->get()
            ->toArray();
    }

    public function getAdultUsers(): array
    {
        return User::where('birth_date', '<=', now()->subYears(18))
            ->where('role', 'client')
            ->get()
            ->toArray();
    }
}
