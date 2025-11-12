<?php
namespace App\Repositories;

use App\Models\Transaction;
use App\Interfaces\ITransaction;

class TransactionRepository implements ITransaction
{
    public function create(array $data): Transaction
    {
        return Transaction::create($data);
    }

    public function getByCompteId(int $compteId)
    {
        return Transaction::where('compte_id', $compteId)->latest()->get();
    }
}