<?php

namespace App\Services;

use App\Interfaces\ICompte;
use App\Models\User;
use App\Models\Compte;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Exception;

class CompteService
{
    protected $compteRepo;

    public function __construct(ICompte $compteRepo)
    {
        $this->compteRepo = $compteRepo;
    }

    /**
     * Crée un utilisateur + son compte associé.
     */
    public function createCompte(array $data)
{
    // Création de l’utilisateur associé
    $user = \App\Models\User::create([
        'nom' => $data['nom'],
        'prenom' => $data['prenom'],
        'telephone' => $data['telephone'],
        'password' => bcrypt($data['password']),
        'type' => $data['type'],
    ]);

    // Génération d’un numéro de compte unique
    $numeroCompte = 'CPT-' . strtoupper(substr($user->nom, 0, 3)) . rand(10000, 99999);

    // Création du compte via le Repository
    $compte = $this->compteRepo->create([
        'user_id' => $user->id,
        'numero_compte' => $numeroCompte,
        'solde' => 0,
        'devise' => 'FCFA',
        'code_pin' => null,
    ]);

    return [
        'id' => $user->id,
        'nom' => $user->nom,
        'prenom' => $user->prenom,
        'telephone' => $user->telephone,
        'numero_compte' => $compte->numero_compte,
        'solde' => $compte->solde,
        'devise' => $compte->devise,
    ];
}
    /**
     * Retourne le solde mis en cache
     */
    public function getSoldeByUserId(int $userId): float
    {
        $cacheKey = "user_{$userId}_solde";
        return Cache::remember($cacheKey, now()->addSeconds(30), function () use ($userId) {
            $compte = $this->compteRepo->findByUserId($userId);
            return $compte ? (float) $compte->solde : 0.0;
        });
    }

    public function refreshSoldeCache(int $userId)
    {
        $cacheKey = "user_{$userId}_solde";
        Cache::forget($cacheKey);
        $this->getSoldeByUserId($userId);
    }

    public function debit(Compte $compte, float $amount): Compte
    {
        $compte->solde -= $amount;
        $compte->save();
        $this->refreshSoldeCache($compte->user_id);
        return $compte;
    }

    public function credit(Compte $compte, float $amount): Compte
    {
        $compte->solde += $amount;
        $compte->save();
        $this->refreshSoldeCache($compte->user_id);
        return $compte;
    }
}
