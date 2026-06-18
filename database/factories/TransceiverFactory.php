<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\Transceiver> */
class TransceiverFactory extends Factory
{
    public function definition(): array
    {
        return [
            'device_id' => $this->faker->numberBetween(1, 100),
            'port_id' => $this->faker->numberBetween(1, 100),
            'index' => (string) $this->faker->numberBetween(1, 100),
            'type' => $this->faker->randomElement(['SFP', 'SFP+', 'QSFP28', 'QSFP+']),
            'vendor' => $this->faker->randomElement(['Cisco', 'Finisar', 'Intel']),
            'model' => 'FTLX' . $this->faker->numerify('####'),
            'serial' => $this->faker->bothify('??######'),
            'channels' => 1,
        ];
    }
}
