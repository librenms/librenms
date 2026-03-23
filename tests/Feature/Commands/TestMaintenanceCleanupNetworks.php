<?php

namespace LibreNMS\Tests\Feature\Commands;

use App\Facades\LibrenmsConfig;
use App\Models\Ipv4Address;
use App\Models\Ipv4Network;
use App\Models\Ipv6Address;
use App\Models\Ipv6Network;
use LibreNMS\Tests\InMemoryDbTestCase;

final class TestMaintenanceCleanupNetworks extends InMemoryDbTestCase
{
    public function testIpv4UnusedNetworksPurged(): void
    {
        LibrenmsConfig::set('networks_purge', true);

        $used = Ipv4Network::factory()->create();
        $unused = Ipv4Network::factory()->create();

        Ipv4Address::factory()->create(['ipv4_network_id' => $used->ipv4_network_id]);

        $this->artisan('maintenance:cleanup-networks')->assertExitCode(0);

        $this->assertDatabaseMissing('ipv4_networks', ['ipv4_network_id' => $unused->ipv4_network_id]);
        $this->assertDatabaseHas('ipv4_networks', ['ipv4_network_id' => $used->ipv4_network_id]);
    }

    public function testIpv6UnusedNetworksPurged(): void
    {
        LibrenmsConfig::set('networks_purge', true);

        $used = Ipv6Network::factory()->create();
        $unused = Ipv6Network::factory()->create();

        Ipv6Address::factory()->create(['ipv6_network_id' => $used->ipv6_network_id]);

        $this->artisan('maintenance:cleanup-networks')->assertExitCode(0);

        $this->assertDatabaseMissing('ipv6_networks', ['ipv6_network_id' => $unused->ipv6_network_id]);
        $this->assertDatabaseHas('ipv6_networks', ['ipv6_network_id' => $used->ipv6_network_id]);
    }

    public function testIpv6OrphanedAddressesDoNotBlockNetworkCleanup(): void
    {
        // Simulate the real-world IPv6 scenario: after the ipv6_addresses migration
        // some rows have ipv6_network_id = 0 (previously NULL/empty). These orphaned
        // rows must NOT prevent unused IPv6 networks from being cleaned up.
        LibrenmsConfig::set('networks_purge', true);

        $unused = Ipv6Network::factory()->create();

        // Insert orphaned IPv6 address rows with ipv6_network_id = 0 (no matching network)
        \DB::table('ipv6_addresses')->insert([
            'ipv6_address' => '2001:0db8:0000:0000:0000:0000:0000:0001',
            'ipv6_compressed' => '2001:db8::1',
            'ipv6_prefixlen' => 64,
            'ipv6_origin' => 'manual',
            'ipv6_network_id' => 0,
            'port_id' => 0,
        ]);

        $this->artisan('maintenance:cleanup-networks')->assertExitCode(0);

        // The unused network should still be deleted — orphaned rows must not count
        $this->assertDatabaseMissing('ipv6_networks', ['ipv6_network_id' => $unused->ipv6_network_id]);
    }

    public function testNoPurgeWhenSettingDisabled(): void
    {
        LibrenmsConfig::set('networks_purge', false);

        $ipv4 = Ipv4Network::factory()->create();
        $ipv6 = Ipv6Network::factory()->create();

        $this->artisan('maintenance:cleanup-networks')->assertExitCode(0);

        $this->assertDatabaseHas('ipv4_networks', ['ipv4_network_id' => $ipv4->ipv4_network_id]);
        $this->assertDatabaseHas('ipv6_networks', ['ipv6_network_id' => $ipv6->ipv6_network_id]);
    }

    public function testForceFlagPurgesEvenWhenSettingDisabled(): void
    {
        LibrenmsConfig::set('networks_purge', false);

        $ipv4 = Ipv4Network::factory()->create();
        $ipv6 = Ipv6Network::factory()->create();

        $this->artisan('maintenance:cleanup-networks', ['--force' => true])->assertExitCode(0);

        $this->assertDatabaseMissing('ipv4_networks', ['ipv4_network_id' => $ipv4->ipv4_network_id]);
        $this->assertDatabaseMissing('ipv6_networks', ['ipv6_network_id' => $ipv6->ipv6_network_id]);
    }
}
