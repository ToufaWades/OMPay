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
 */

use App\Http\Controllers\Controller;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Jobs\SendPinSmsJob;
use App\Traits\ApiResponse;

class AuthController extends Controller
{
    use ApiResponse;


    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $result = $this->authService->register($data);
        if (!$result['success']) {
            return $this->apiResponse(false, null, $result['message'], 400);
        }
    SendPinSmsJob::dispatch($result['user']['telephone'], $result['code_pin']);
    return $this->apiResponse(true, $result['user'], 'Inscription réussie, code PIN envoyé par SMS', 201);
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
