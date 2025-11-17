<?php

namespace App\Services;

use App\Models\User;
use App\Models\Compte;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $data)
    {
        $telephone = $data['telephone'];
        if (preg_match('/^(77|78)\d{7}$/', $telephone)) {
            $telephone = '+221' . $telephone;
        }
        elseif (!preg_match('/^\+2217[78]\d{7}$/', $telephone)) {
            // Format incorrect, on peut lever une erreur ou corriger
            $telephone = '+22177' . substr($telephone, -7); // fallback 77
        }
        $userData = [
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'telephone' => $telephone,
            'password' => bcrypt($data['password']),
            'type' => $data['type'],
            'status' => 'inactif',
        ];

        // Création de l'utilisateur
        $user = User::create($userData);

        if ($user->type === 'client') {
            // ✅ Toujours générer un code PIN en string
            $codePin = (string) str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

            // ✅ Création du compte
            $compte = Compte::create([
                'user_id' => $user->id,
                'numero_compte' => 'CPT-' . strtoupper(substr($user->nom, 0, 3)) . rand(10000, 99999),
                'solde' => 0,
                'devise' => 'FCFA',
                'code_pin' => $codePin,
                'qr_code' => 'QRCODE' . rand(1000, 9999),
            ]);

            return [
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'nom' => $user->nom,
                    'prenom' => $user->prenom,
                    'telephone' => $user->telephone,
                    'type' => $user->type,
                    'numero_compte' => $compte->numero_compte,
                    'solde' => $compte->solde,
                    'devise' => $compte->devise,
                    'qr_code' => $compte->qr_code,
                ],
                'code_pin' => $codePin,
                'message' => 'Utilisateur client créé avec succès',
            ];
        }

        // ✅ Distributeur : pas de compte associé
        return [
            'success' => true,
            'user' => [
                'id' => $user->id,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'telephone' => $user->telephone,
                'type' => $user->type,
            ],
            'message' => 'Utilisateur distributeur créé',
        ];
    }

    public function login(array $data)
    {
        // Formatage du numéro de téléphone pour la recherche
        $telephone = $data['telephone'];
        if (preg_match('/^(77|78)\d{7}$/', $telephone)) {
            $telephone = '+221' . $telephone;
        }
        $user = User::where('telephone', $telephone)->first();

        // Vérification téléphone + mot de passe
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return ['success' => false, 'message' => 'Identifiants invalides'];
        }

        // Première connexion : activation via code PIN
        if ($user->status === 'inactif') {
            if ($user->type === 'client') {
                $compte = $user->compte;
                if (!$compte) {
                    return ['success' => false, 'message' => 'Aucun compte associé'];
                }
                if (!isset($data['code_pin']) || (string)$compte->code_pin !== (string)$data['code_pin']) {
                    return ['success' => false, 'message' => 'Code PIN invalide'];
                }
            }
            $user->status = 'activé';
            $user->save();
        }

        $token = $user->createToken('api-token')->plainTextToken;
        $refreshToken = bin2hex(random_bytes(32));

        return [
            'success' => true,
            'data' => [
                'token' => $token,
                'refresh_token' => $refreshToken,
            ],
            'message' => 'Connexion effectué avec succès!'
        ];
    }
}

