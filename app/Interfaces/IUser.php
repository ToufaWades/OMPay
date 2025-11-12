<?php
namespace App\Interfaces;

use App\Models\User;

interface IUser
{
    public function create(array $data): User;
    public function findByPhone(string $phone): ?User;
    public function find(int $id): ?User;
}