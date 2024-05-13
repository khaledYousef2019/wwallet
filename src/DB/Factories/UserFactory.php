<?php

namespace App\DB\Factories;

use Faker\Factory as Faker;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\DB\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        $faker = Faker::create();

        return [
            'name' => $faker->name,
            'email' => $faker->email,
            'password' => $faker->password,
        ];
    }
}
