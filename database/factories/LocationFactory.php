<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Location::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'location' => $this->faker->randomElement([
                $this->faker->sentence($this->faker->numberBetween(1, 10)),
                str_replace("\n", ' ', $this->faker->address),
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
                'lat' => $this->faker->latitude,
                'lng' => $this->faker->longitude,
            ];
        });
    }
}
