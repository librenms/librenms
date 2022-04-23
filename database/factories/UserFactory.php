<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'auth_type' => 'mysql',
            'username' => $this->faker->unique()->userName,
            'realname' => $this->faker->name,
            'email' => $this->faker->safeEmail,
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'level' => 1,
        ];
    }

    public function admin()
    {
        return $this->state(function () {
            return [
                'level' => '10',
            ];
        });
    }

    public function read()
    {
        return $this->state(function () {
            return [
                'level' => '5',
            ];
        });
    }
}
