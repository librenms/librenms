<?php

namespace LibreNMS\Tests\Feature;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Tests\DBTestCase;
use LibreNMS\Util\Stats;

final class StatsTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testPortAssociationModeUsesTheDefaultWithoutAnOverride(): void
    {
        LibrenmsConfig::set('default_port_association_mode', 'ifIndex');
        $this->snmpDevice([]);
        $this->snmpDevice([]);
        $this->snmpDevice(['port_association_mode' => 'ifName']);
        DevicePollingMethod::factory()->create([
            'device_id' => Device::factory()->create()->device_id,
            'method_type' => PollingMethodType::Icmp, // ping only devices have no ports
        ]);

        $portAssoc = (new Stats)->dump()['data']['port_assoc'];

        $this->assertInstanceOf(Collection::class, $portAssoc);
        $this->assertEquals([1 => 2, 2 => 1], $portAssoc->pluck('total', 'port_association_mode')->all()); // ifIndex => 2, ifName => 1, reported as the mode id
    }

    public function testSnmpVersionFollowsTheSecretTypeAndVersionSetting(): void
    {
        $community = Secret::create(['description' => 'stats community', 'secret_type' => SecretType::SnmpCommunity, 'data' => ['community' => 'public']]);
        $v3 = Secret::create(['description' => 'stats v3', 'secret_type' => SecretType::SnmpV3, 'data' => ['authlevel' => 'noAuthNoPriv', 'authname' => 'user']]);
        $this->snmpDevice([], $community);
        $this->snmpDevice(['version' => 'v2c'], $community);
        $this->snmpDevice(['version' => 'v1', 'port' => 1161], $community);
        $this->snmpDevice(['version' => 'v1'], $v3); // a v3 secret is always v3
        $this->snmpDevice([], $v3);
        $this->snmpDevice([]); // no secret, polled as v2c

        $versions = (new Stats)->dump()['data']['snmp_version'];

        $this->assertEqualsCanonicalizing(['v2c' => 3, 'v1' => 1, 'v3' => 2], $versions->pluck('total', 'snmpver')->all());
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    private function snmpDevice(array $settings, ?Secret $secret = null): void
    {
        DevicePollingMethod::factory()->create([
            'device_id' => Device::factory()->create()->device_id,
            'method_type' => PollingMethodType::Snmp,
            'settings' => $settings,
            'secret_id' => $secret?->id,
        ]);
    }
}
