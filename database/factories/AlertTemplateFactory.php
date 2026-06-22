<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\AlertTemplate> */
class AlertTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(3, true),
            'template' => $this->faker->text(),
            'title' => $this->faker->sentence(),
            'title_rec' => $this->faker->sentence(),
        ];
    }
}
