<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\PortAdsl> */
class PortAdslFactory extends Factory
{
    public function definition(): array
    {
        return [
            'port_id' => $this->faker->numberBetween(1, 100),
            'adslLineCoding' => 'DMT',
            'adslLineType' => 'interleave',
            'adslAtucCurrSnrMgn' => $this->faker->randomFloat(1, 0, 60),
            'adslAtucCurrAtn' => $this->faker->randomFloat(1, 0, 60),
            'adslAtucCurrOutputPwr' => $this->faker->randomFloat(1, 0, 30),
            'adslAtucCurrAttainableRate' => $this->faker->numberBetween(1000, 50000),
            'adslAtucChanCurrTxRate' => $this->faker->numberBetween(1000, 50000),
            'adslAturChanCurrTxRate' => $this->faker->numberBetween(100, 5000),
            'adslAturCurrSnrMgn' => $this->faker->randomFloat(1, 0, 60),
            'adslAturCurrAtn' => $this->faker->randomFloat(1, 0, 60),
            'adslAturCurrOutputPwr' => $this->faker->randomFloat(1, 0, 30),
            'adslAturCurrAttainableRate' => $this->faker->numberBetween(100, 5000),
        ];
    }
}
