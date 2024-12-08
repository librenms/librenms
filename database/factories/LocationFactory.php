<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Location> */
class LocationFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'location' => $this->faker->randomElement([
                $this->faker->sentence($this->faker->numberBetween(1, 10)),
                str_replace("\n", ' ', $this->faker->address()),
            ]),
        ];
    }

    /**
     * Indicate add lat,lng
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withCoordinates()
    {
        return $this->state(function (array $attributes) {
            return [
                'lat' => $this->faker->latitude(),
                'lng' => $this->faker->longitude(),
            ];
        });
    }
}
