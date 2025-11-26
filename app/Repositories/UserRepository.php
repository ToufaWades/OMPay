<?php
namespace App\Repositories;

use App\Models\User;
use App\Interfaces\IUser;

class UserRepository implements IUser
{
    public function create(array $data): User
    {
        return User::create($data);
    }

    public function findByPhone(string $phone): ?User
    {
        return User::where('telephone', $phone)->first();
    }

    public function find(int $id): ?User
    {
        return User::find($id);
    }
}