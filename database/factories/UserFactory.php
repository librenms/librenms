<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\User> */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'auth_type' => 'mysql',
            'username' => $this->faker->unique()->userName(),
            'realname' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        ];
    }

    public function admin(): UserFactory
    {
        return $this->afterCreating(function ($user) {
            $user->assignRole('admin');
        });
    }

    public function read(): UserFactory
    {
        return $this->afterCreating(function ($user) {
            $user->assignRole('global-read');
        });
    }
}
