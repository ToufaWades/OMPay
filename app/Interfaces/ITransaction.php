<?php

namespace App\Interfaces;

use App\Models\Transaction;

interface ITransaction
{
    public function create(array $data): Transaction;
    public function getByCompteId(int $compteId);
}
