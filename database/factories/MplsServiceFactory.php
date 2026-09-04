<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<\App\Models\MplsService> */
class MplsServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'device_id' => Device::factory(),
            'svc_oid' => $this->faker->numberBetween(1, 1000),
            'svcRowStatus' => 'active',
            'svcType' => 'tls',
            'svcCustId' => $this->faker->numberBetween(1, 100),
            'svcAdminStatus' => 'up',
            'svcOperStatus' => 'up',
            'svcDescription' => 'SVC_' . $this->faker->word(),
            'svcMtu' => 1514,
            'svcNumSaps' => 1,
            'svcNumSdps' => 1,
            'svcLastMgmtChange' => $this->faker->unixTime(),
            'svcLastStatusChange' => $this->faker->unixTime(),
            'svcVRouterId' => null,
            'svcTlsMacLearning' => 'enabled',
            'svcTlsStpAdminStatus' => 'enabled',
            'svcTlsStpOperStatus' => 'up',
            'svcTlsFdbTableSize' => 2048,
            'svcTlsFdbNumEntries' => 50,
        ];
    }

    public function up(): static
    {
        return $this->state([
            'svcAdminStatus' => 'up',
            'svcOperStatus' => 'up',
        ]);
    }

    public function down(): static
    {
        return $this->state([
            'svcAdminStatus' => 'down',
            'svcOperStatus' => 'down',
        ]);
    }

    public function vpls(): static
    {
        return $this->state(['svcType' => 'tls']);
    }

    public function epipe(): static
    {
        return $this->state([
            'svcType' => 'epipe',
            'svcTlsMacLearning' => null,
            'svcTlsStpAdminStatus' => null,
            'svcTlsStpOperStatus' => null,
            'svcTlsFdbTableSize' => null,
            'svcTlsFdbNumEntries' => null,
        ]);
    }

    public function vprn(): static
    {
        return $this->state([
            'svcType' => 'vprn',
            'svcTlsMacLearning' => null,
            'svcTlsStpAdminStatus' => null,
            'svcTlsStpOperStatus' => null,
            'svcTlsFdbTableSize' => null,
            'svcTlsFdbNumEntries' => null,
        ]);
    }

    public function ies(): static
    {
        return $this->state([
            'svcType' => 'ies',
            'svcTlsMacLearning' => null,
            'svcTlsStpAdminStatus' => null,
            'svcTlsStpOperStatus' => null,
            'svcTlsFdbTableSize' => null,
            'svcTlsFdbNumEntries' => null,
        ]);
    }
}
