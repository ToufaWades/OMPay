<?php
namespace App\Services;

use App\Interfaces\ITransaction;
use App\Interfaces\ICompte;
use App\Models\Transaction;
use App\Jobs\ProcessTransactionJob;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    protected $txRepo;
    protected $compteRepo;
    protected $compteService;


    public function __construct(
        ITransaction $txRepo,
        ICompte $compteRepo,
        CompteService $compteService
    ) {
        $this->txRepo = $txRepo;
        $this->compteRepo = $compteRepo;
        $this->compteService = $compteService;
    }

    /**
     * Create transaction (paiement or transfert).
     * Distinction par $payload['type'].
     * For transfer by number: use numero_destinataire
     * For merchant pay: use code_marchand
     */
    public function createTransaction(array $payload): Transaction
    {
        // Business validations (done also in Requests)
        return DB::transaction(function () use ($payload) {
            $compte = $this->compteRepo->findByUserId($payload['user_id']);
            if (!$compte) {
                throw new \Exception("Compte introuvable");
            }

            if ($compte->solde < $payload['montant']) {
                throw new \Exception("Solde insuffisant");
            }

            // Reserve funds instantly (optimistic)
            $compte->solde -= $payload['montant'];
            $compte->save();
            $this->compteService->refreshSoldeCache($compte->user_id);

            // create transaction in DB (status en_attente)
            $tx = $this->txRepo->create([
                'compte_id' => $compte->id,
                'montant' => $payload['montant'],
                'numero_destinataire' => $payload['numero_destinataire'] ?? null,
                'code_distributeur' => $payload['code_distributeur'] ?? null,
                'code_marchand' => $payload['code_marchand'] ?? null,
                'type' => $payload['type'],
                'status' => 'en_attente',
                'date_transaction' => now(),
            ]);

            ProcessTransactionJob::dispatch($tx->id);

            return $tx;
        });
    }
}
