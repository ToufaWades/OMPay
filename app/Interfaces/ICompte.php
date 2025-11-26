<?php
namespace App\Interfaces;

use App\Models\Compte;

interface ICompte
{
    public function create(array $data): Compte;
    public function findByUserId(int $userId): ?Compte;
    public function findByNumero(string $numero): ?Compte;
    public function updateSolde(Compte $compte, float $amount): Compte;
}