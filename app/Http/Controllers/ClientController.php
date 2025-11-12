<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Http\Requests\PaiementRequest;
use App\Http\Requests\TransfertRequest;
use App\Services\ClientService;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponse;
use App\Http\Requests\DepotRequest;

class ClientController extends Controller
{
    use ApiResponse;

/**
 * @var \App\Services\ClientService
 */
    protected $clientService;
    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    // POST /client/depot
    public function depot(DepotRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();
        $result = $this->clientService->depot($user, $validated['montant']);
        if (!$result['success']) {
            return $this->apiResponse(false, null, $result['message'], 400);
        }
        return $this->apiResponse(true, $result['data'], 'Dépôt effectué', 200);
    }
    public function paiement(PaiementRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();
        $result = $this->clientService->paiement($user, $validated);
        if (!$result['success']) {
            return $this->apiResponse(false, null, $result['message'], 400);
        }
        return $this->apiResponse(true, $result['data'], 'Paiement effectué', 200);
    }

    // POST /client/transfert
    public function transfert(TransfertRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();
        $result = $this->clientService->transfert($user, $validated);
        if (!$result['success']) {
            return $this->apiResponse(false, null, $result['message'], 400);
        }
        return $this->apiResponse(true, $result['data'], 'Transfert effectué', 200);
    }

    // GET /client/solde
    public function solde()
    {
        $user = Auth::user();
        $result = $this->clientService->solde($user);
        if (!$result['success']) {
            return $this->apiResponse(false, null, $result['message'], 400);
        }
        return $this->apiResponse(true, $result['data'], 'Solde récupéré', 200);
    }

    // GET /client/transactions
    public function transactions()
    {
        $user = Auth::user();
        $result = $this->clientService->transactions($user);
        if (!$result['success']) {
            return $this->apiResponse(false, null, $result['message'], 400);
        }
        return $this->apiResponse(true, $result['data'], 'Historique récupéré', 200);
    }

    // GET /client/profil
    public function profil()
    {
        $user = Auth::user();
        $result = $this->clientService->profil($user);
        if (!$result['success']) {
            return $this->apiResponse(false, null, $result['message'], 400);
        }
        return $this->apiResponse(true, $result['data'], 'Profil récupéré', 200);
    }
}
