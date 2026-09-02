<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\MplsService;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\MplsSap> */
class MplsSapFactory extends Factory
{
    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'svc_id' => MplsService::factory(),
            'svc_oid' => fn (array $attributes) => MplsService::find($attributes['svc_id'])?->svc_oid ?? $this->faker->numberBetween(1, 1000),
            'sapPortId' => $this->faker->numberBetween(1, 100),
            'ifName' => '0/' . $this->faker->numberBetween(1, 24),
            'sapEncapValue' => (string) $this->faker->numberBetween(1, 4094),
            'sapRowStatus' => 'active',
            'sapType' => 'tls',
            'sapDescription' => 'SAP_' . $this->faker->word(),
            'sapAdminStatus' => 'up',
            'sapOperStatus' => 'up',
            'sapLastMgmtChange' => $this->faker->unixTime(),
            'sapLastStatusChange' => $this->faker->unixTime(),
        ];
    }

    public function up(): static
    {
        return $this->state([
            'sapAdminStatus' => 'up',
            'sapOperStatus' => 'up',
        ]);
    }

    public function down(): static
    {
        return $this->state([
            'sapAdminStatus' => 'down',
            'sapOperStatus' => 'down',
        ]);
    }
}
