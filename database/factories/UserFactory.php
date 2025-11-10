<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        $phonePrefixes = ['77','78'];
        $prefix = $this->faker->randomElement($phonePrefixes);

        return [
            'nom' => $this->faker->lastName(),
            'prenom' => $this->faker->firstName(),
            'telephone' => $prefix . $this->faker->numerify('#######'),
            'password' => bcrypt('password'), 
            'type' => 'client', 
        ];
    }

    public function distributor()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'distributeur', 
            ];
        });
    }
}