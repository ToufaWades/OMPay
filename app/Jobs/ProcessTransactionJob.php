<?php
namespace App\Jobs;

use App\Models\Compte;
use App\Models\Transaction as TransactionModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessTransactionJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, Dispatchable;

    public $txId;

    public function __construct(int $txId)
    {
        $this->txId = $txId;
    }

    public function handle()
    {
        $tx = TransactionModel::find($this->txId);
        if (!$tx) return;

        try {
            sleep(1);

            if ($tx->type === 'transfert' && $tx->numero_destinataire) {
                $destCompte = Compte::where('numero_compte', $tx->numero_destinataire)->first();
                if ($destCompte) {
                    $destCompte->solde += $tx->montant;
                    $destCompte->save();
                } else {
                    $origin = $tx->compte;
                    $origin->solde += $tx->montant;
                    $origin->save();
                    $tx->status = 'échoué';
                    $tx->save();
                    return;
                }
            }

            $tx->status = 'terminé';
            $tx->save();

        } catch (\Throwable $e) {
            $origin = $tx->compte;
            if ($origin) {
                $origin->solde += $tx->montant;
                $origin->save();
            }
            $tx->status = 'échoué';
            $tx->save();
        }
    }
}