<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Compte;
use App\Models\Transaction;

class DistributeurSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $distributeurs = [
            [
                'nom' => 'Fall',
                'prenom' => 'Mamadou',
                'telephone' => '+221770000001',
                'password' => Hash::make('123456'),
            ],
            [
                'nom' => 'Diop',
                'prenom' => 'Aminata',
                'telephone' => '+221770000002',
                'password' => Hash::make('123456'),
            ],
            [
                'nom' => 'Ba',
                'prenom' => 'Ibrahima',
                'telephone' => '+221770000003',
                'password' => Hash::make('123456'),
            ],
        ];

        foreach ($distributeurs as $data) {

            $user = User::create(array_merge($data, [
                'type' => 'distributeur',
            ]));

            $codeDistributeur = 'DST' . rand(1000, 9999);

            $compte = Compte::create([
                'user_id' => $user->id,
                'numero_compte' => '+221' . rand(770000000, 789999999),
                'solde' => rand(100000, 500000),
                'devise' => 'FCFA',
                'qr_code' => 'QRCODE' . rand(1000, 9999),
                'code_pin' => rand(100000, 999999),
            ]);

            Transaction::create([
                'compte_id' => $compte->id,
                'montant' => rand(5000, 20000),
                'code_distributeur' => $codeDistributeur,
                'code_marchand' => 'MRC' . rand(1000, 9999),
                'type' => 'paiement',
                'status' => 'terminé',
                'date_transaction' => now(),
            ]);
        }
    }
}
