<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\MefInfo> */
class MefInfoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'mefID' => $this->faker->unique()->numberBetween(1, 1000),
            'mefType' => 'epl',
            'mefIdent' => 'MEF-' . $this->faker->word(),
            'mefMTU' => 1522,
            'mefAdmState' => 'unlocked',
            'mefRowState' => 'active',
        ];
    }
}
