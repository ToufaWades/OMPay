<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Client;
use App\Models\User;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
   protected $model = Client::class;


    public function definition()
    {
        $phonePrefixes = ['77','78'];
        $prefix = $this->faker->randomElement($phonePrefixes);

        return [
            'user_id' => User::factory(),
            'nom' => $this->faker->lastName(),
            'prenom' => $this->faker->firstName(),
            'telephone' => $prefix . $this->faker->numerify('#######'),
            'pays' => $this->faker->city(),
        ];
    }
}
