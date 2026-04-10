<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Availability> */
class AvailabilityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'device_id' => $this->faker->numberBetween(1, 100),
            'duration' => $this->faker->randomElement([86400, 604800, 2592000, 31536000]),
            'availability_perc' => $this->faker->randomFloat(4, 95, 100),
        ];
    }
}
