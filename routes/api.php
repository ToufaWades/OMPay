<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DistributeurController;

Route::prefix('v1')->group(function () {
    // Authentification
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    // Client
    Route::middleware(['auth:sanctum', 'role:client'])->group(function () {
        Route::post('client/paiement', [ClientController::class, 'paiement']);
        Route::post('client/transfert', [ClientController::class, 'transfert']);
        Route::post('client/depot', [ClientController::class, 'depot']);
        Route::get('client/solde', [ClientController::class, 'solde']);
        Route::get('client/transactions', [ClientController::class, 'transactions']);
        Route::get('client/profil', [ClientController::class, 'profil']);
    });

    // Distributeur
    Route::middleware(['auth:sanctum', 'role:distributeur'])->group(function () {
        Route::post('distributeur/depot', [DistributeurController::class, 'depot']);
        Route::post('distributeur/retrait', [DistributeurController::class, 'retrait']);
    });
});
