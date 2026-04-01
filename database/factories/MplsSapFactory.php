<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\MplsSap> */
class MplsSapFactory extends Factory
{
    public function definition(): array
    {
        return [
            'svc_id' => $this->faker->numberBetween(1, 100),
            'svc_oid' => $this->faker->numberBetween(1, 1000),
            'sapPortId' => $this->faker->numberBetween(1, 100),
            'sapDescription' => 'SAP_' . $this->faker->word(),
            'sapAdminStatus' => 'up',
            'sapOperStatus' => 'up',
        ];
    }
}
