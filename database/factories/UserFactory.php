<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Silber\Bouncer\BouncerFacade as Bouncer;

/** @extends Factory<\App\Models\User> */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
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

    public function admin()
    {
        return $this->afterCreating(function ($user) {
            Bouncer::allow('admin')->everything();
            $user->assign('admin');
        });
    }

    public function read()
    {
        return $this->afterCreating(function ($user) {
            Bouncer::allow(Bouncer::role()->firstOrCreate(['name' => 'global-read'], ['title' => 'Global Read']))
                ->to('viewAny', '*', []);
            $user->assign('global-read');
        });
    }
}
