<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\DeviceOutage> */
class DeviceOutageFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $start = $this->faker->unique()->numberBetween(strtotime('-30 days'), time());
        $isOpen = $this->faker->boolean(60);

        return [
            'going_down' => $start,
            'up_again' => $isOpen ? null : $this->faker->dateTimeBetween("@{$start}", 'now')->getTimestamp(),
        ];
    }

    /**
     * State for an open outage (no up_again yet).
     */
    public function open(): self
    {
        return $this->state(fn () => [
            'up_again' => null,
        ]);
    }

    /**
     * State for a closed/resolved outage (up_again after going_down).
     */
    public function closed(): self
    {
        return $this->state(function (array $attributes) {
            $start = $attributes['going_down'] ?? $this->faker->unique()->numberBetween(strtotime('-30 days'), time());

            return [
                'going_down' => $start,
                'up_again' => $this->faker->dateTimeBetween("@{$start}", 'now')->getTimestamp(),
            ];
        });
    }
}
