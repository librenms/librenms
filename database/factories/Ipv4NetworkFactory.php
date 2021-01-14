<?php

namespace Database\Factories;

use App\Models\Ipv4Network;
use Illuminate\Database\Eloquent\Factories\Factory;

class Ipv4NetworkFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Ipv4Network::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'ipv4_network' => $this->faker->ipv4 . '/' . $this->faker->numberBetween(0, 32),
        ];
    }
}
