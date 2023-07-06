<?php

namespace Database\Factories;

use App\Models\Component;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Component> */
class ComponentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'device_id' => $this->faker->randomDigit(),
            'type' => $this->faker->regexify('[A-Za-z0-9]{4,20}'),
        ];
    }
}
