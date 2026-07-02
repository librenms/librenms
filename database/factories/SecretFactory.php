<?php

namespace Database\Factories;

use App\Models\Secret;
use Illuminate\Database\Eloquent\Factories\Factory;
use LibreNMS\Enum\SecretType;

/** @extends Factory<Secret> */
class SecretFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Secret>
     */
    protected $model = Secret::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'secret_type' => $this->faker->randomElement(SecretType::cases()),
            'description' => $this->faker->text(),
            'data' => ['username' => 'username', 'password' => 'password'],
        ];
    }
}
