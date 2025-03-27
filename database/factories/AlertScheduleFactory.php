<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\AlertSchedule> */
class AlertScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->name(),
            'notes' => $this->faker->text(),
            'recurring' => 0,
        ];
    }

    public function recurring()
    {
        return $this->state(function () {
            return [
                'recurring' => 1,
            ];
        });
    }
}
