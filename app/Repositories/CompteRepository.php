<?php
namespace App\Repositories;

use App\Models\Compte;
use App\Interfaces\ICompte;

class CompteRepository implements ICompte
{
       public function create(array $data): Compte
    {
        return Compte::create($data);
    }

    public function findByUserId(int $userId): ?Compte
    {
        return Compte::where('user_id', $userId)->first();
    }

    public function findByNumero(string $numero): ?Compte
    {
        return Compte::where('numero_compte', $numero)->first();
    }

    public function updateSolde(Compte $compte, float $amount): Compte
    {
        $compte->solde = $amount;
        $compte->save();
        return $compte;
    }
}