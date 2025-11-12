<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Services\CompteService;
use App\Http\Requests\CompteRequest; // ✅ Corrige le namespace
use Illuminate\Http\Request;

class CompteController extends Controller
{
    use ApiResponse;

    protected $compteService;

    public function __construct(CompteService $compteService)
    {
        $this->compteService = $compteService;
    }

    /**
     * @OA\Post(
     *     path="/compte",
     *     tags={"Compte"},
     *     summary="Créer un compte (client ou distributeur)",
     *     description="Permet de créer un compte utilisateur avec un numéro de compte unique.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nom", "prenom", "telephone", "password", "type"},
     *             @OA\Property(property="nom", type="string", example="Fall"),
     *             @OA\Property(property="prenom", type="string", example="Awa"),
     *             @OA\Property(property="telephone", type="string", example="+221771234567"),
     *             @OA\Property(property="password", type="string", example="123456"),
     *             @OA\Property(property="type", type="string", enum={"client","distributeur"}, example="client")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compte créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte créé avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=5),
     *                 @OA\Property(property="nom", type="string", example="Fall"),
     *                 @OA\Property(property="prenom", type="string", example="Awa"),
     *                 @OA\Property(property="telephone", type="string", example="+221771234567"),
     *                 @OA\Property(property="numero_compte", type="string", example="CPT-FAL12345"),
     *                 @OA\Property(property="solde", type="number", example=0),
     *                 @OA\Property(property="devise", type="string", example="FCFA")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Erreur de validation"),
     *     @OA\Response(response=500, description="Erreur serveur")
     * )
     */
    public function store(CompteRequest $request)
    {
        $validated = $request->validated();
        $result = $this->compteService->createCompte($validated);
        return $this->success($result, 'Compte créé avec succès', 201);
    }

    /**
     * @OA\Get(
     *     path="/compte",
     *     tags={"Compte"},
     *     summary="Afficher les informations du compte connecté",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Informations du compte",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nom", type="string", example="Fall"),
     *                 @OA\Property(property="prenom", type="string", example="Awa"),
     *                 @OA\Property(property="telephone", type="string", example="+221771234567"),
     *                 @OA\Property(property="numero_compte", type="string", example="CPT-FAL12345"),
     *                 @OA\Property(property="solde", type="number", example=50000),
     *                 @OA\Property(property="devise", type="string", example="FCFA")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non autorisé")
     * )
     */
    public function show(Request $req)
    {
        $user = $req->user();
        $compte = $user->compte;

        if (!$compte) {
            return $this->error('Compte non trouvé', 404);
        }

        return $this->success([
            'id' => $user->id,
            'nom' => $user->nom,
            'prenom' => $user->prenom,
            'telephone' => $user->telephone,
            'numero_compte' => $compte->numero_compte,
            'solde' => $compte->solde,
            'devise' => $compte->devise,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/compte/solde",
     *     summary="Afficher le solde du compte connecté",
     *     tags={"Compte"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Solde du compte",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="solde", type="number", example=50000),
     *                 @OA\Property(property="devise", type="string", example="FCFA")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non autorisé")
     * )
     */
    public function balance(Request $req)
    {
        $user = $req->user();
        $solde = $this->compteService->getSoldeByUserId($user->id);
        return $this->success(['solde' => $solde, 'devise' => 'FCFA']);
    }

    /**
     * @OA\Get(
     *     path="/compte/history",
     *     summary="Lister les transactions du compte connecté",
     *     tags={"Compte"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Historique des transactions",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="type", type="string", example="transfert"),
     *                     @OA\Property(property="montant", type="number", example=1500),
     *                     @OA\Property(property="status", type="string", example="terminé"),
     *                     @OA\Property(property="date_transaction", type="string", format="date-time", example="2025-11-10T14:00:00Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Non autorisé")
     * )
     */
    public function history(Request $req)
    {
        $compte = $req->user()->compte;
        $transactions = $compte ? $compte->transactions()->latest()->get() : [];
        return $this->success($transactions);
    }
}
