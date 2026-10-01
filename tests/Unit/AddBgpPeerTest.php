<?php

namespace LibreNMS\Tests\Unit;

use App\Models\BgpPeer;
use App\Models\Device;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;

final class AddBgpPeerTest extends DBTestCase
{
    use DatabaseTransactions;

    /**
     * The os specific modules (vrp, cumulus, firebrick, timos, dell-os10) run before the
     * generic module and used to omit context_name, which stored null. The generic module
     * looks for an empty string, so it could not see those rows and inserted a second one.
     */
    public function testOsModuleCreateStoresEmptyContextNotNull(): void
    {
        $device = Device::factory()->create();

        $peer = $device->bgppeers()->create($this->osModulePeerAttributes('10.0.0.1'));

        $this->assertSame('', $peer->fresh()->context_name);
    }

    public function testPeerFromOsModuleIsNotDuplicatedByGenericDiscovery(): void
    {
        $device = Device::factory()->create();
        $device->bgppeers()->create($this->osModulePeerAttributes('10.0.0.2'));

        $this->discover($device, '10.0.0.2');

        $this->assertSame(1, $this->peerCount($device, '10.0.0.2'));
    }

    public function testRepeatedDiscoveryDoesNotAddRows(): void
    {
        $device = Device::factory()->create();

        $this->discover($device, '10.0.0.3');
        $this->discover($device, '10.0.0.3');
        $this->discover($device, '10.0.0.3');

        $this->assertSame(1, $this->peerCount($device, '10.0.0.3'));
    }

    public function testPeerInNamedContextIsStillSeparate(): void
    {
        $device = Device::factory()->create();
        BgpPeer::factory()->create([
            'device_id' => $device->device_id,
            'bgpPeerIdentifier' => '10.0.0.4',
            'context_name' => 'vrf-a',
        ]);

        $this->discover($device, '10.0.0.4', 'vrf-b');

        $this->assertSame(2, $this->peerCount($device, '10.0.0.4'));
    }

    /**
     * @return array<string, mixed>
     */
    private function osModulePeerAttributes(string $ip): array
    {
        return [
            'context_name' => '',
            'bgpPeerIdentifier' => $ip,
            'bgpPeerRemoteAs' => 65001,
            'bgpPeerState' => 'established',
            'bgpPeerAdminStatus' => 'start',
            'bgpLocalAddr' => '0.0.0.0',
            'bgpPeerRemoteAddr' => $ip,
            'bgpPeerInUpdates' => 0,
            'bgpPeerOutUpdates' => 0,
            'bgpPeerInTotalMessages' => 0,
            'bgpPeerOutTotalMessages' => 0,
            'bgpPeerFsmEstablishedTime' => 0,
            'bgpPeerInUpdateElapsedTime' => 0,
            'astext' => 'AS65001',
        ];
    }

    private function discover(Device $device, string $ip, string $context = ''): void
    {
        add_bgp_peer(
            ['device_id' => $device->device_id, 'context_name' => $context],
            ['ip' => $ip, 'as' => 65001, 'astext' => 'AS65001', 'localip' => '10.0.0.254']
        );
    }

    private function peerCount(Device $device, string $ip): int
    {
        return BgpPeer::where('device_id', $device->device_id)
            ->where('bgpPeerIdentifier', $ip)
            ->count();
    }
}
