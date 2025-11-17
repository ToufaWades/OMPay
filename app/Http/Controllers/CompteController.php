<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaiementRequest;
use App\Http\Requests\TransfertRequest;
use App\Services\CompteService;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponse;
use App\Http\Requests\DepotRequest;

/**
 * @OA\Tag(
 *     name="Compte",
 *     description="Opérations liées aux comptes"
 * )
 */
class CompteController extends Controller
{
    use ApiResponse;

    /**
     * @var \App\Services\CompteService
     */
    protected $compteService;
    public function __construct(CompteService $compteService)
    {
        $this->compteService = $compteService;
    }
/**
 * @OA\Post(
 *     path="/comptes/{id}/depot",
 *     tags={"Compte"},
 *     summary="Effectuer un dépôt sur un compte",
 *     operationId="depotCompte",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID du compte",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"montant"},
 *             @OA\Property(property="montant", type="number", example=1000)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Dépôt effectué"
 *     )
 * )
 */ 
    public function depot(DepotRequest $request, $id)
    {
        $user = Auth::user();
        $validated = $request->validated();
    $result = $this->compteService->depot($user, $validated['montant'], $id);
        if (!$result['success']) {
            return $this->apiResponse(false, null, $result['message'], 400);
        }
        // Ajout du signe + pour le montant dans la réponse
        if (isset($result['data']['montant'])) {
            $result['data']['montant'] = ($result['data']['montant'] >= 0 ? '+' : '-') . abs($result['data']['montant']);
        }
        return $this->apiResponse(true, $result['data'], 'Dépôt effectué', 200);
    }

/**
 * @OA\Post(
 *     path="/comptes/{id}/paiement",
 *     tags={"Compte"},
 *     summary="Effectuer un paiement depuis un compte",
 *     operationId="paiementCompte",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID du compte",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"montant", "code_marchand"},
 *             @OA\Property(property="montant", type="number", example=1000),
 *             @OA\Property(property="code_marchand", type="string", example="MRC1234")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Paiement effectué")
 * )
 */
    /**
     * @OA\Post(
     *     path="/paiement-marchand",
     *     tags={"Compte"},
     *     summary="Effectuer un paiement vers un marchand (par code_marchand ou téléphone)",
     *     operationId="paiementMarchand",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"montant"},
     *             @OA\Property(property="montant", type="number", example=1000),
     *             @OA\Property(property="code_marchand", type="string", example="MRC1234"),
     *             @OA\Property(property="telephone", type="string", example="+221771428150")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Paiement marchand effectué")
     * )
     */
    public function paiementMarchand(PaiementRequest $request)
    {
        $validated = $request->validated();
        $result = $this->compteService->paiementMarchand($validated);
        if (!$result['success']) {
            return $this->apiResponse(false, null, $result['message'], 400);
        }
        return $this->apiResponse(true, $result, 'Paiement marchand effectué', 200);
    }

    public function paiement(PaiementRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();
    $result = $this->compteService->paiement($user, $validated);
        if (!$result['success']) {
            return $this->apiResponse(false, null, $result['message'], 400);
        }
        // Ajout du signe - pour le montant dans la réponse
        if (isset($result['data']['montant'])) {
            $result['data']['montant'] = ($result['data']['montant'] >= 0 ? '+' : '-') . abs($result['data']['montant']);
        }
        return $this->apiResponse(true, $result['data'], 'Paiement effectué', 200);
    }

            /**
             * @OA\Post(
             *     path="/comptes/{id}/transfert",
             *     tags={"Compte"},
             *     summary="Effectuer un transfert entre comptes",
             *     operationId="transfertCompte",
             *     @OA\Parameter(
             *         name="id",
             *         in="path",
             *         required=true,
             *         description="ID du compte source",
             *         @OA\Schema(type="integer", example=1)
             *     ),
             *     @OA\RequestBody(
             *         required=true,
             *         @OA\JsonContent(
             *             @OA\Property(property="montant", type="number", example=1000),
             *             @OA\Property(property="numero", type="string", example="+221770112233")
             *         )
             *     ),
             *     @OA\Response(response=200, description="Transfert effectué")
             * )
             */
    public function transfert(TransfertRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();
    $result = $this->compteService->transfert($user, $validated);
        if (!$result['success']) {
            return $this->apiResponse(false, null, $result['message'], 400);
        }
        // Ajout du signe - pour le montant dans la réponse
        if (isset($result['data']['montant'])) {
            $result['data']['montant'] = ($result['data']['montant'] >= 0 ? '+' : '-') . abs($result['data']['montant']);
        }
        return $this->apiResponse(true, $result['data'], 'Transfert effectué', 200);
    }

            /**
             * @OA\Get(
             *     path="/comptes/{id}/solde",
             *     tags={"Compte"},
             *     summary="Afficher le solde d'un compte",
             * operationId="soldeCompte",
             *     @OA\Parameter(
             *         name="id",
             *         in="path",
             *         required=true,
             *         description="ID du compte",
             *         @OA\Schema(type="integer", example=1)
             *     ),
             *     @OA\Response(response=200, description="Solde récupéré")
             * )
             */
    public function solde($id)
    {
        $user = Auth::user();
    $result = $this->compteService->solde($user, $id);
        if (!$result['success']) {
            return $this->apiResponse(false, null, $result['message'], 400);
        }
        return $this->apiResponse(true, $result['data'], 'Solde récupéré', 200);
    }

    /**
     * @OA\Get(
     *     path="/comptes/{id}/transactions",
     *     tags={"Compte"},
     *     summary="Afficher les transactions d'un compte",
     *     operationId="transactionsCompte",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du compte",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Historique récupéré",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="transactions", type="array",
     *                     @OA\Items(type="object")
     *                 ),
     *                 @OA\Property(property="pagination", type="object",
     *                     @OA\Property(property="current_page", type="integer", example=1),
     *                     @OA\Property(property="total_pages", type="integer", example=3),
     *                     @OA\Property(property="per_page", type="integer", example=10),
     *                     @OA\Property(property="total_items", type="integer", example=25)
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Historique récupéré")
     *         )
     *     )
     * )
     */
    public function transactions($id)
    {
        $user = Auth::user();
        $result = $this->compteService->transactions($user, $id);
        if (!$result['success']) {
            return $this->apiResponse(false, null, $result['message'], 400);
        }
        // Inclure la pagination dans la réponse
        $responseData = [
            'transactions' => $result['data'],
            'pagination' => $result['pagination'] ?? null
        ];
        return $this->apiResponse(true, $responseData, 'Historique récupéré', 200);
    }

            /**
             * @OA\Get(
             *     path="/comptes/{id}/profil",
             *     tags={"Compte"},
             *     summary="Afficher le profil d'un compte",
             * operationId="profilCompte",
             *     @OA\Parameter(
             *         name="id",
             *         in="path",
             *         required=true,
             *         description="ID du compte",
             *         @OA\Schema(type="integer", example=1)
             *     ),
             *     @OA\Response(response=200, description="Profil récupéré")
             * )
             */
    public function profil()
    {
        $user = Auth::user();
    $result = $this->compteService->profil($user);
        if (!$result['success']) {
            return $this->apiResponse(false, null, $result['message'], 400);
        }
        return $this->apiResponse(true, $result['data'], 'Profil récupéré', 200);
    }

    // Endpoint /api/compte
    public function compte()
    {
        $user = Auth::user();
    $result = $this->compteService->compte($user);
        if (!$result['success']) {
            return $this->apiResponse(false, null, $result['message'], 400);
        }
        return response()->json($result, 200);
    }
}