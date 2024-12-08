<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Component> */
class ComponentFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'device_id' => $this->faker->randomDigit(),
            'type' => $this->faker->regexify('[A-Za-z0-9]{4,20}'),
        ];
    }
}
