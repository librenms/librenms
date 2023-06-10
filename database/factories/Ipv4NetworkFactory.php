<?php

namespace Database\Factories;

use App\Models\Ipv4Network;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Ipv4Network> */
class Ipv4NetworkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'ipv4_network' => $this->faker->ipv4() . '/' . $this->faker->numberBetween(0, 32),
        ];
    }
}
