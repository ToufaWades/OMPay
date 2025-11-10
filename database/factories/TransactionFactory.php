<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Transaction;
use App\Models\Compte;
use Illuminate\Support\Str;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
   protected $model = Transaction::class;

    public function definition()
    {
        $types = ['transfert','paiement'];
        $status = ['en_attente','terminé','échoué'];


        return [
            'compte_id' => Compte::factory(), 
            'montant' => $this->faker->randomFloat(1, 100, 50000),
            'numero_destinataire' => $this->faker->randomElement([ 
                '+221' . $this->faker->numberBetween(770000000, 789999999), 
                null 
            ]),

            'code_marchand' => $this->faker->randomElement([
                'MRC' . $this->faker->numberBetween(1000, 9999), 
                null
            ]),
            'type' => $this->faker->randomElement($types),
            'code_distributeur' => 'DST' . $this->faker->numberBetween(1000, 9999),
            'status' => $this->faker->randomElement($status),
            'date_transaction' => $this->faker->dateTimeBetween('-1 month','now'),
        ];
    }
}
