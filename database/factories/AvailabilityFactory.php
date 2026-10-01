<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Availability> */
class AvailabilityFactory extends Factory
{
    public function definition(): array
    {
        // (device_id, duration) is a unique key. A process-local counter keeps durations
        // distinct within a batch without relying on faker's global unique() state (which
        // accumulates across the whole test run and can exhaust a small value pool).
        static $seq = 0;
        $durations = [86400, 604800, 2592000, 31536000];

        return [
            'device_id' => $this->faker->numberBetween(1, 100),
            'duration' => $durations[$seq++ % count($durations)],
            'availability_perc' => $this->faker->randomFloat(4, 95, 100),
        ];
    }
}
