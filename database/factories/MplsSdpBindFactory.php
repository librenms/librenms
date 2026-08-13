<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\MplsSdpBind> */
class MplsSdpBindFactory extends Factory
{
    public function definition(): array
    {
        return [
            'sdp_id' => $this->faker->numberBetween(1, 100),
            'svc_id' => $this->faker->numberBetween(1, 100),
            'sdp_oid' => $this->faker->numberBetween(1, 1000),
            'svc_oid' => $this->faker->numberBetween(1, 1000),
            'sdpBindRowStatus' => 'active',
            'sdpBindAdminStatus' => 'up',
            'sdpBindOperStatus' => 'up',
        ];
    }
}
