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

            /**
             * @OA\Post(
             *     path="/distributeur/depot",
             *     tags={"Distributeur"},
             *     summary="Effectuer un dépôt sur le compte d'un client (montant positif)",
             *     operationId="depotDistributeur",
             *     @OA\RequestBody(
             *         required=true,
             *         @OA\JsonContent(
             *             required={"numero", "montant"},
             *             @OA\Property(property="numero", type="string", example="+221771428150"),
             *             @OA\Property(property="montant", type="number", example=3000)
             *         )
             *     ),
             *     @OA\Response(
             *         response=200,
             *         description="Dépôt effectué",
             *         @OA\JsonContent(
             *             @OA\Property(property="success", type="boolean", example=true),
             *             @OA\Property(property="data", type="object",
             *                 @OA\Property(property="id", type="integer", example=1),
             *                 @OA\Property(property="type", type="string", example="depot"),
             *                 @OA\Property(property="montant", type="number", example=3000, description="Montant positif"),
             *                 @OA\Property(property="status", type="string", example="terminé"),
             *                 @OA\Property(property="date_transaction", type="string", example="2025-11-16 12:00:00")
             *             ),
             *             @OA\Property(property="message", type="string", example="Dépôt effectué")
             *         )
             *     )
             * )
             */
    public function depot(DepotRequest $request)
    {
        $user = Auth::user();
        $result = $this->distributeurService->depot(array_merge($request->validated(), ['user' => $user]));
        if (!$result['success']) {
            return $this->apiResponse(false, null, $result['message'], 400);
        }
        return $this->apiResponse(true, $result['data'], 'Dépôt effectué', 200);
    }

            /**
             * @OA\Post(
             *     path="/distributeur/retrait",
             *     tags={"Distributeur"},
             *     summary="Effectuer un retrait sur le compte d'un client (montant négatif)",
             *     operationId="retraitDistributeur",
             *     @OA\RequestBody(
             *         required=true,
             *         @OA\JsonContent(
             *             required={"qr_code", "montant"},
             *             @OA\Property(property="qr_code", type="string", example="QRCODE5442"),
             *             @OA\Property(property="montant", type="number", example=3000)
             *         )
             *     ),
             *     @OA\Response(
             *         response=200,
             *         description="Retrait effectué",
             *         @OA\JsonContent(
             *             @OA\Property(property="success", type="boolean", example=true),
             *             @OA\Property(property="data", type="object",
             *                 @OA\Property(property="id", type="integer", example=1),
             *                 @OA\Property(property="type", type="string", example="retrait"),
             *                 @OA\Property(property="solde_retiré", type="number", example=3000, description="Montant retiré (positif dans la réponse, négatif en base)"),
             *                 @OA\Property(property="solde_restant", type="number", example=12000),
             *                 @OA\Property(property="numero_distributeur", type="string", example="+221770000001"),
             *                 @OA\Property(property="frais", type="number", example=0),
             *                 @OA\Property(property="telephone_client", type="string", example="+221771428150"),
             *                 @OA\Property(property="status", type="string", example="terminé"),
             *                 @OA\Property(property="date_transaction", type="string", example="2025-11-16 12:00:00")
             *             ),
             *             @OA\Property(property="message", type="string", example="Retrait effectué")
             *         )
             *     )
             * )
             */
    public function retrait(RetraitRequest $request)
    {
        $user = Auth::user();
        $result = $this->distributeurService->retrait(array_merge($request->validated(), ['user' => $user]));
        if (!$result['success']) {
            return $this->apiResponse(false, null, $result['message'], 400);
        }
        $notif = $result['notification'] ?? null;
        if ($notif) {
            SendRetraitNotificationJob::dispatch($notif['telephone'], $notif['montant'], $notif['numero_distributeur'], $notif['frais']);
        }
        return $this->apiResponse(true, $result['data'], 'Retrait effectué', 200);
    }
}
