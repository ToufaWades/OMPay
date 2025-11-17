<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompteController;
use App\Http\Controllers\DistributeurController;

Route::prefix('v1')->group(function () {
    // Authentification
        Route::post('/auth/register', [AuthController::class, 'register']);
        Route::post('/auth/login', [AuthController::class, 'login']);
        Route::post('/auth/connexion', [AuthController::class, 'connexion']);

    // Compte
    Route::middleware(['auth:sanctum', 'role:client'])->group(function () {
    Route::post('comptes/{id}/paiement', [CompteController::class, 'paiement']);
        Route::post('paiement-marchand', [CompteController::class, 'paiementMarchand']);
    Route::post('comptes/{id}/transfert', [CompteController::class, 'transfert']);
        Route::post('comptes/{id}/depot', [CompteController::class, 'depot']);
        Route::get('comptes/{id}/solde', [CompteController::class, 'solde']);
        Route::get('comptes/{id}/transactions', [CompteController::class, 'transactions']);
    Route::get('comptes/{id}/profil', [CompteController::class, 'profil']);
        Route::get('api/compte', [CompteController::class, 'compte']);
    });

    // Distributeur
    Route::middleware(['auth:sanctum', 'role:distributeur'])->group(function () {
        Route::post('distributeur/depot', [DistributeurController::class, 'depot']);
        Route::post('distributeur/retrait', [DistributeurController::class, 'retrait']);
    });
});
