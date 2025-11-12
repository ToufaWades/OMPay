<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Http\Requests\DepotRequest;
use App\Http\Requests\RetraitRequest;
use App\Services\DistributeurService;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendRetraitNotificationJob;
use App\Traits\ApiResponse;

class DistributeurController extends Controller
{
    use ApiResponse;


    protected $distributeurService;

    public function __construct(DistributeurService $distributeurService)
    {
        $this->distributeurService = $distributeurService;
    }

    // POST /distributeur/depot
    public function depot(DepotRequest $request)
    {
        $user = Auth::user();
        $result = $this->distributeurService->depot(array_merge($request->validated(), ['user' => $user]));
        if (!$result['success']) {
            return $this->apiResponse(false, null, $result['message'], 400);
        }
        return $this->apiResponse(true, $result['data'], 'Dépôt effectué', 200);
    }

    // POST /distributeur/retrait
    public function retrait(RetraitRequest $request)
    {
        $user = Auth::user();
        $result = $this->distributeurService->retrait(array_merge($request->validated(), ['user' => $user]));
        if (!$result['success']) {
            return $this->apiResponse(false, null, $result['message'], 400);
        }
        // Envoi notification SMS au client
        $notif = $result['notification'] ?? null;
        if ($notif) {
            SendRetraitNotificationJob::dispatch($notif['telephone'], $notif['montant'], $notif['numero_distributeur'], $notif['frais']);
        }
        return $this->apiResponse(true, $result['data'], 'Retrait effectué', 200);
    }
}
