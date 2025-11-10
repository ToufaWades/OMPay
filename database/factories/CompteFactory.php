<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Compte;
use App\Models\User;
use Illuminate\Support\Str;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Compte>
 */
class CompteFactory extends Factory
{
    protected $model = Compte::class;

    public function definition()
    {
       $prefixes = ['77', '78'];
        $prefix = $this->faker->randomElement($prefixes);

        return [
            'user_id' => User::factory(),
            'numero_compte' => '+221' . $prefix . $this->faker->numberBetween(1000000, 9999999),
            'solde' => $this->faker->numberBetween(0, 200000),
            'devise' => 'FCFA',
            'code_pin' => str_pad($this->faker->numberBetween(0, 9999), 4, '0', STR_PAD_LEFT),

        ];
    }
}
