<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\MplsService> */
class MplsServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'svc_oid' => $this->faker->numberBetween(1, 1000),
            'svcRowStatus' => 'active',
            'svcType' => 'epipe',
            'svcAdminStatus' => 'up',
            'svcOperStatus' => 'up',
            'svcDescription' => 'SVC_' . $this->faker->word(),
        ];
    }
}
