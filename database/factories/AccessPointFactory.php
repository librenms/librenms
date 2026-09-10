<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\AccessPoint> */
class AccessPointFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'AP-' . $this->faker->word(),
            'radio_number' => $this->faker->numberBetween(0, 3),
            'type' => $this->faker->randomElement(['a', 'b', 'g', 'n', 'ac', 'ax']),
            'mac_addr' => $this->faker->macAddress(),
            'channel' => $this->faker->numberBetween(1, 165),
            'txpow' => $this->faker->numberBetween(1, 30),
            'radioutil' => $this->faker->numberBetween(0, 100),
            'numasoclients' => $this->faker->numberBetween(0, 100),
            'nummonclients' => 0,
            'numactbssid' => $this->faker->numberBetween(1, 8),
            'nummonbssid' => 0,
            'interference' => $this->faker->numberBetween(0, 100),
        ];
    }
}
