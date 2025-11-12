<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TransactionRequest;
use App\Traits\ApiResponse;
use App\Services\TransactionService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    use ApiResponse;

    protected $txService;

    public function __construct(TransactionService $txService)
    {
        $this->txService = $txService;
    }

    /**
 * @OA\Post(
 *     path="/transaction",
 *     summary="Effectuer un transfert ou un paiement",
 *     tags={"Transaction"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"type", "montant"},
 *             @OA\Property(property="type", type="string", enum={"transfert", "paiement"}),
 *             @OA\Property(property="numero_destinataire", type="string", example="+221770112233"),
 *             @OA\Property(property="code_marchand", type="string", example="MRC1234"),
 *             @OA\Property(property="montant", type="number", example=1000)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Transaction effectuée",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Transfert réussi"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=12),
 *                 @OA\Property(property="type", type="string", example="transfert"),
 *                 @OA\Property(property="montant", type="number", example=2000),
 *                 @OA\Property(property="status", type="string", example="terminé")
 *             )
 *         )
 *     ),
 *     @OA\Response(response=400, description="Erreur de validation ou solde insuffisant")
 * )
 */
    public function create(TransactionRequest $req)
    {
        $user = $req->user();
        $payload = $req->validated();
        $payload['user_id'] = $user->id;

        try {
            $tx = $this->txService->createTransaction($payload);
            return $this->success($tx, 'Transaction initiée', 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 400);
        }
    }
}