<?php
namespace App\Services;

use App\Models\Transaction;
use App\Models\User;

class CompteService
{
    public function depot($user, $montant, $id)
    {
        if (!$user) {
            return ['success' => false, 'message' => 'Utilisateur non trouvé'];
        }
    $compte = \App\Models\Compte::find($id);
        if (!$compte) {
            return ['success' => false, 'message' => 'Compte introuvable'];
        }
        $compte->solde += $montant;
        $compte->save();
        $transaction = Transaction::create([
            'compte_id' => $compte->id,
            'type' => 'depot',
            'montant' => $montant,
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

    public function paiement($user, array $data, $id)
    {
        if (!$user) {
            return ['success' => false, 'message' => 'Utilisateur non trouvé'];
        }
    $compte = \App\Models\Compte::find($id);
        if (!$compte) {
            return ['success' => false, 'message' => 'Compte introuvable'];
        }
        if ($compte->solde < $data['montant']) {
            return ['success' => false, 'message' => 'Solde insuffisant'];
        }
        $compte->solde -= $data['montant'];
        $compte->save();
        $transaction = Transaction::create([
            'compte_id' => $compte->id,
            'type' => 'paiement',
            'montant' => -$data['montant'],
            'code_marchand' => $data['code_marchand'],
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
                'telephone_client' => $user->telephone,
            ]
        ];
    }

    public function transfert($user, array $data, $id)
    {
        if (!$user) {
            return ['success' => false, 'message' => 'Utilisateur non trouvé'];
        }
    $compte = \App\Models\Compte::find($id);
        if (!$compte) {
            return ['success' => false, 'message' => 'Compte introuvable'];
        }
        if ($compte->solde < $data['montant']) {
            return ['success' => false, 'message' => 'Solde insuffisant'];
        }
        $compte->solde -= $data['montant'];
        $compte->save();
        $destCompte = \App\Models\Compte::whereHas('user', function($q) use ($data) {
            $q->where('telephone', $data['numero']);
        })->first();
        if (!$destCompte) {
            return ['success' => false, 'message' => 'Compte destinataire introuvable'];
        }
        $destCompte->solde += $data['montant'];
        $destCompte->save();
        $transaction = \App\Models\Transaction::create([
            'compte_id' => $compte->id,
            'type' => 'transfert',
            'montant' => -$data['montant'],
            'numero_destinataire' => $data['numero'],
            'status' => 'terminé',
            'date_transaction' => now(),
        ]);
        // Transaction côté destinataire
        \App\Models\Transaction::create([
            'compte_id' => $destCompte->id,
            'type' => 'transfert',
            'montant' => $data['montant'],
            'numero_destinataire' => $user->telephone,
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
                'telephone_client' => $user->telephone,
            ]
        ];
    }

    public function solde($user, $id)
    {
    $compte = \App\Models\Compte::find($id);
        if (!$compte) {
            return ['success' => false, 'message' => 'Compte introuvable'];
        }
        return [
            'success' => true,
            'data' => [
                'solde' => $compte->solde,
                'prenom' => $user->prenom,
                'qr_code' => $compte->qr_code,
            ]
        ];
    }

    public function transactions($user, $id)
    {
        $compte = \App\Models\Compte::find($id);
        if (!$compte) {
            return ['success' => false, 'message' => 'Compte introuvable'];
        }
        $transactions = $compte->transactions()->orderByDesc('date_transaction')->paginate(10);
        $result = $transactions->map(function($t) use ($user) {
            return [
                'id' => $t->id,
                'type' => $t->type,
                'montant' => $t->montant,
                'status' => $t->status,
                'date_transaction' => $t->date_transaction,
                'telephone_client' => optional($t->compte->user)->telephone,
            ];
        });
        return [
            'success' => true,
            'data' => $result,
            'pagination' => [
                'current_page' => $transactions->currentPage(),
                'total_pages' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total_items' => $transactions->total(),
            ]
        ];
    }

    // Endpoint /api/compte
    public function compte($user)
    {
        $compte = $user->compte;
        $transactions = $compte ? $compte->transactions()->orderByDesc('date_transaction')->take(10)->get() : collect();
        $result = [
            'success' => true,
            'data' => [
                'user' => [
                    'telephone' => $user->telephone
                ],
                'compte' => [
                    'solde' => $compte ? $compte->solde : 0,
                    'nom' => 'Compte Principal',
                    'qr_code' => $compte ? $compte->qr_code : null
                ],
                'transactions' => $transactions->map(function($t) {
                    return [
                        'success' => true,
                        'data' => [
                            'id' => $t->id,
                            'type' => $t->type,
                            'montant' => $t->montant,
                            'status' => $t->status,
                            'date_transaction' => $t->date_transaction,
                            'numero_destinataire' => $t->numero_destinataire ?? null,
                            'numero_paiement' => $t->code_marchand ?? null
                        ]
                    ];
                })
            ]
        ];
        return $result;
    }

    public function profil($user)
    {
        return [
            'success' => true,
            'data' => [
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'telephone' => $user->telephone,
            ]
        ];
    }
}