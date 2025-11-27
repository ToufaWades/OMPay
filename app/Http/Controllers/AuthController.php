<?php
namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="OMPay API",
 *     version="1.0.0",
 *     description="Documentation de l'API OMPay (Orange Money Pay)"
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8000/api/v1",
 *     description="Serveur local"
 * )
 * @OA\Server(
 *     url="https://ompaye-5jg4.onrender.com/api/v1",
 *     description="Serveur de production"
 * )
 */

use App\Http\Controllers\Controller;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Jobs\SendPinSmsJob;
use App\Traits\ApiResponse;
use App\Http\Requests\LoginSansPinRequest;

class AuthController extends Controller
{


    /**
     * Connexion sans code PIN (compte déjà activé)
     */
            /**
             * @OA\Post(
             *     path="/auth/connexion",
             *     tags={"Authentification"},
             *     summary="Connexion utilisateur sans code PIN (compte déjà activé)",
             *     @OA\RequestBody(
             *         required=true,
             *         @OA\MediaType(
             *             mediaType="application/json",
             *             @OA\Schema(
             *                 required={"telephone", "password"},
             *                 @OA\Property(property="telephone", type="string", example="+221771428150"),
             *                 @OA\Property(property="password", type="string", example="123456")
             *             )
             *         )
             *     ),
             *     @OA\Response(
             *         response=200,
             *         description="Connexion effectué avec succès!",
             *         @OA\JsonContent(
             *             @OA\Property(property="token", type="string", example="eyJhbGciOi..."),
             *             @OA\Property(property="refresh_token", type="string", example="eyJhbGciOi..."),
             *             @OA\Property(property="message", type="string", example="Connexion effectué avec succès!")
             *         )
             *     ),
             *     @OA\Response(response=401, description="Identifiants invalides"),
             *     @OA\Response(response=403, description="Compte non activé")
             * )
             */
    public function connexion(LoginSansPinRequest $request)
    {
        $data = $request->validated();
        $user = \App\Models\User::where('telephone', $data['telephone'])->first();
        if (!$user || !\Illuminate\Support\Facades\Hash::check($data['password'], $user->password)) {
            return $this->apiResponse(false, null, 'Identifiants invalides', 401);
        }
        if ($user->status !== 'activé') {
            $user->status = 'activé';
            $user->save();
        }
        $token = $user->createToken('api-token')->plainTextToken;
        $refreshToken = bin2hex(random_bytes(32));
        return response()->json([
            'token' => $token,
            'refresh_token' => $refreshToken,
            'message' => 'Connexion effectué avec succès!'
        ], 200);
    }
    use ApiResponse;
    protected $authService;
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @OA\Post(
     *     path="/auth/register",
     *     tags={"Authentification"},
     *     summary="Inscription utilisateur (client/distributeur)",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"nom", "prenom", "telephone", "password", "type"},
     *                 @OA\Property(property="nom", type="string", example="Fall"),
     *                 @OA\Property(property="prenom", type="string", example="Awa"),
     *                 @OA\Property(property="telephone", type="string", example="+221771234567"),
     *                 @OA\Property(property="password", type="string", example="123456"),
     *                 @OA\Property(property="type", type="string", enum={"client", "distributeur"}, example="client")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Compte créé avec succès!",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Compte créé avec succès!")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Erreur de validation")
     * )
     */
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $result = $this->authService->register($data);
        if (!$result['success']) {
            return $this->apiResponse(false, null, $result['message'], 400);
        }
        if (
            isset($result['code_pin']) &&
            isset($result['user']['type']) &&
            $result['user']['type'] === 'client'
        ) {
            SendPinSmsJob::dispatch($result['user']['telephone'], $result['code_pin']);
            return response()->json([
                'success' => true,
                'message' => 'Compte créé avec succès!',
                'code_pin' => $result['code_pin']
            ], 201);
        }
        return response()->json([
            'success' => true,
            'message' => 'Compte créé avec succès!'
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();
        $result = $this->authService->login($data);
        if (!$result['success']) {
            return $this->apiResponse(false, null, $result['message'], 401);
        }
        return $this->apiResponse(true, $result['data'], 'Connexion réussie', 200);
    }
}
