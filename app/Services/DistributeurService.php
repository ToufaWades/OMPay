<?php
namespace App\Services;

use App\Models\Compte;
use App\Models\Transaction;

class DistributeurService
{

    public function depot(array $data)
    {
        $user = $data['user'];
        // Trouver le compte du client destinataire
        $destCompte = Compte::whereHas('user', function($q) use ($data) {
            $q->where('telephone', $data['numero']);
        })->first();
        if (!$destCompte) {
            return ['success' => false, 'message' => 'Compte client introuvable'];
        }
        // Créditer le compte client
        $destCompte->solde += $data['montant'];
        $destCompte->save();
        // Créer la transaction (montant positif)
        $montant = ($data['montant'] > 0 ? '+' : '-') . abs($data['montant']);
        $transaction = Transaction::create([
            'compte_id' => $destCompte->id,
            'type' => 'depot',
            'montant' => $montant,
            'code_distributeur' => $user->telephone,
            'status' => 'terminé',
            'date_transaction' => now(),
        ]);
        return [
            'success' => true,
            'data' => [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'montant' => '+' . abs($transaction->montant),
                'status' => $transaction->status,
                'date_transaction' => $transaction->date_transaction,
            ]
        ];
    }

    public function retrait(array $data)
    {
        $user = $data['user'];
        // Trouver le compte client par QR code
        $compte = \App\Models\Compte::where('qr_code', $data['qr_code'])->first();
        if (!$compte) {
            return ['success' => false, 'message' => 'Compte client introuvable'];
        }
        if ($compte->solde < $data['montant']) {
            return ['success' => false, 'message' => 'Solde insuffisant'];
        }
        // Débiter le compte client
        $solde_retiré = $data['montant'];
        $compte->solde -= $solde_retiré;
        $compte->save();
        // Créer la transaction (montant négatif)
        $montant = ($solde_retiré > 0 ? '-' : '+') . abs($solde_retiré);
        $transaction = \App\Models\Transaction::create([
            'compte_id' => $compte->id,
            'type' => 'retrait',
            'montant' => $montant,
            'code_distributeur' => $user->telephone,
            'status' => 'terminé',
            'date_transaction' => now(),
        ]);
        $frais = 0; // À adapter si besoin
        return [
            'success' => true,
            'data' => [
                'id' => $transaction->id,
                'type' => $transaction->type,
                'solde_retiré' => '-' . abs($solde_retiré),
                'solde_restant' => $compte->solde,
                'numero_distributeur' => $user->telephone,
                'frais' => $frais,
                'telephone_client' => $compte->user->telephone,
                'status' => $transaction->status,
                'date_transaction' => $transaction->date_transaction,
            ]
        ];
    }
}
