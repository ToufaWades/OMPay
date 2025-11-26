<?php

namespace App\Services;

use App\Interfaces\ICompte;
use App\Models\User;
use App\Models\Compte;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Exception;

class CompteService {
    /**
     * Retourne les infos du compte connecté
     */
    public function compte($user)
    {
        $compte = $this->compteRepo->findByUserId($user->id);
        if (!$compte) {
            return ['success' => false, 'message' => 'Compte introuvable'];
        }
        return [
            'success' => true,
            'data' => [
                'user' => [
                    'telephone' => $user->telephone,
                    'prenom' => $user->prenom
                ],
                'compte' => [
                    'id' => $compte->id,
                    'solde' => $compte->solde,
                    'nom' => $compte->numero_compte,
                    'qr_code' => $compte->qr_code ?? null
                ],
                'transactions' => \App\Models\Transaction::where('compte_id', $compte->id)->get()
            ]
        ];
    }

    /**
     * Retourne le profil d'un compte
     */
    public function profil($user)
    {
        $compte = $this->compteRepo->findByUserId($user->id);
        if (!$compte) {
            return ['success' => false, 'message' => 'Compte introuvable'];
        }
        return [
            'success' => true,
            'data' =>  [
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'telephone' => $user->telephone,
                ]
        ];
    }
    /**
     * Effectue un paiement vers un marchand (crédit du compte marchand)
     * $data doit contenir soit code_marchand soit telephone, et montant
     */
    public function paiementMarchand(array $data)
    {
        // Recherche du compte marchand par code_marchand ou téléphone
        if (!empty($data['code_marchand'])) {
            $compte = Compte::where('code_marchand', $data['code_marchand'])->first();
            if (!$compte) {
                return [
                    'success' => false,
                    'message' => 'Marchand introuvable',
                ];
            }
        } elseif (!empty($data['telephone'])) {
            $compte = Compte::where('telephone', $data['telephone'])->first();
            if (!$compte) {
                return [
                    'success' => false,
                    'message' => 'Compte avec ce téléphone introuvable',
                ];
            }
        } else {
            return [
                'success' => false,
                'message' => 'Aucun identifiant de paiement fourni',
            ];
        }

        // Créditer le compte du marchand
        $this->credit($compte, $data['montant']);
        $montant = ($data['montant'] > 0 ? '+' : '-') . abs($data['montant']);
        $transaction = \App\Models\Transaction::create([
            'compte_id' => $compte->id,
            'type' => 'paiement',
            'montant' => $montant,
            'code_marchand' => $data['code_marchand'] ?? null,
            'telephone' => $data['telephone'] ?? null,
            'status' => 'terminé',
            'date_transaction' => now(),
        ]);
        return [
            'success' => true,
            'message' => 'Paiement effectué avec succès',
            'solde' => $compte->solde,
            'transaction_id' => $transaction->id,
        ];
    }

    /**
     * Effectue un paiement depuis le compte donné
     */
    public function paiement($user, array $data)
    {
        $compte = $this->compteRepo->findByUserId($user->id);
        if (!$compte) {
            return ['success' => false, 'message' => 'Compte introuvable'];
        }
        if ($compte->solde < $data['montant']) {
            return ['success' => false, 'message' => 'Solde insuffisant'];
        }
        // Débit du compte (montant négatif)
        $this->debit($compte, $data['montant']);
        $transaction = \App\Models\Transaction::create([
            'compte_id' => $compte->id,
            'type' => 'paiement',
            'montant' => -abs($data['montant']),
            'code_marchand' => $data['code_marchand'] ?? null,
            'status' => 'terminé',
            'date_transaction' => now(),
        ]);
        return [
            'success' => true,
            'data' => [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'montant' => $transaction->montant,
                'status' => $transaction->status,
                'date_transaction' => $transaction->date_transaction,
            ]
        ];
    }

    /**
     * Effectue un transfert entre deux comptes
     */
    public function transfert($user, array $data)
    {
        $compte = $this->compteRepo->findByUserId($user->id);
        if (!$compte) {
            return ['success' => false, 'message' => 'Compte introuvable'];
        }
        if ($compte->solde < $data['montant']) {
            return ['success' => false, 'message' => 'Solde insuffisant'];
        }
        // Débit du compte source (montant négatif)
        $this->debit($compte, $data['montant']);
        // Recherche du compte destinataire par téléphone
        $destUserId = $this->getUserIdByTelephone($data['numero']);
        if (!$destUserId) {
            // Créer un nouvel utilisateur et compte destinataire si inexistant
            $newUser = \App\Models\User::create([
                'nom' => 'Destinataire',
                'prenom' => '',
                'telephone' => $data['numero'],
                'password' => bcrypt('default123'),
                'type' => 'client',
            ]);
            $numeroCompte = 'CPT-' . strtoupper(substr($newUser->nom, 0, 3)) . rand(10000, 99999);
            $destCompte = $this->compteRepo->create([
                'user_id' => $newUser->id,
                'numero_compte' => $numeroCompte,
                'solde' => 0,
                'devise' => 'FCFA',
                'code_pin' => null,
            ]);
        } else {
            $destCompte = $this->compteRepo->findByUserId($destUserId);
        }
        // Crédit du compte destinataire (montant positif)
        $this->credit($destCompte, $data['montant']);
        // Transaction négative pour l'envoyeur uniquement
        $transactionEnvoyeur = \App\Models\Transaction::create([
            'compte_id' => $compte->id,
            'type' => 'transfert',
            'montant' => -abs($data['montant']),
            'numero_destinataire' => $data['numero'],
            'status' => 'terminé',
            'date_transaction' => now(),
        ]);
        return [
            'success' => true,
            'data' => [
                'id' => $transactionEnvoyeur->id,
                'type' => $transactionEnvoyeur->type,
                'montant' => ($transactionEnvoyeur->montant > 0 ? '+' : '') . $transactionEnvoyeur->montant,
                'status' => $transactionEnvoyeur->status,
                'date_transaction' => $transactionEnvoyeur->date_transaction,
                'numero_destinataire' => $data['numero'],
            ]
        ];
    }

    private function getUserIdByTelephone($telephone)
    {
        $user = User::where('telephone', $telephone)->first();
        return $user ? $user->id : null;
    }
    protected $compteRepo;

    public function __construct(ICompte $compteRepo)
    {
        $this->compteRepo = $compteRepo;
    }
    /**
     * Retourne les transactions paginées du compte
     */
    public function transactions($user, $id)
    {
        $compte = $this->compteRepo->findByUserId($user->id);
        if (!$compte || $compte->id != $id) {
            return ['success' => false, 'message' => 'Compte introuvable ou non autorisé'];
        }
        $perPage = request()->get('per_page', 10);
        $page = request()->get('page', 1);
        $query = \App\Models\Transaction::where('compte_id', $compte->id)->orderByDesc('date_transaction');
        $totalItems = $query->count();
        $transactions = $query->skip(($page - 1) * $perPage)->take($perPage)->get();
        $totalPages = ceil($totalItems / $perPage);
        return [
            'success' => true,
            'data' => $transactions,
            'pagination' => [
                'current_page' => (int)$page,
                'total_pages' => (int)$totalPages,
                'per_page' => (int)$perPage,
                'total_items' => (int)$totalItems
            ]
        ];
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
           return Cache::remember($cacheKey, now()->addSeconds(30), function () use ($userId) 
            {
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
    /**
     * Retourne le solde du compte
     */
    public function solde($user, $id)
    {
        $compte = $this->compteRepo->findByUserId($user->id);
        if (!$compte || $compte->id != $id) {
            return ['success' => false, 'message' => 'Compte introuvable ou non autorisé'];
        }
        return [
            'success' => true,
            'data' => [
                'solde' => $compte->solde,
                'devise' => $compte->devise
            ]
        ];
    }
    
}