<?php
namespace App\Services;

use App\Models\Transaction;
use App\Models\User;


class ClientService
{
    public function depot($user, $montant)
    {
        if (!$user) {
            return ['success' => false, 'message' => 'Utilisateur non trouvé'];
        }
        $compte = $user->compte;
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

    public function paiement($user, array $data)
    {
        if (!$user) {
            return ['success' => false, 'message' => 'Utilisateur non trouvé'];
        }
        $compte = $user->compte;
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
            'montant' => $data['montant'],
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

    public function transfert($user, array $data)
    {
        if (!$user) {
            return ['success' => false, 'message' => 'Utilisateur non trouvé'];
        }
        $compte = $user->compte;
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
            'montant' => $data['montant'],
            'numero_destinataire' => $data['numero'],
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

    public function solde($user)
    {
        $compte = $user->compte;
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

    public function transactions($user)
    {
        $compte = $user->compte;
        if (!$compte) {
            return ['success' => false, 'message' => 'Compte introuvable'];
        }
        $transactions = $compte->transactions()->orderByDesc('date_transaction')->get();
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
            'data' => $result
        ];
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