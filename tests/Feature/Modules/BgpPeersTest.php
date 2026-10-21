<?php

namespace LibreNMS\Tests\Feature\Modules;

use App\Facades\DeviceCache;
use App\Models\BgpPeer;
use App\Models\Device;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Data\Source\Snmp\RawSnmpResponse;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Modules\LegacyModule;
use LibreNMS\OS;
use LibreNMS\Tests\DBTestCase;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use SnmpQuery;

final class BgpPeersTest extends DBTestCase
{
    use DatabaseTransactions;

    #[DataProvider('counter64Provider')]
    public function testTimosCounter64(string $received, string $sent, int $expectedReceived, int $expectedSent): void
    {
        $device = Device::factory()->create(['os' => 'timos']);
        $peer = BgpPeer::factory()->create([
            'device_id' => $device->device_id,
            'bgpPeerIdentifier' => '192.0.2.1',
            'bgpPeerState' => 'established',
            'bgpPeerAdminStatus' => 'start',
            'bgpPeerFsmEstablishedTime' => 100,
            'context_name' => '',
        ]);

        // Simulate net-snmp's -OQU output, keeping Counter64 values as decimal strings.
        $index = '1.1.4.192.0.2.1';
        $response = new RawSnmpResponse(implode("\n", [
            "TIMETRA-BGP-MIB::tBgpPeerNgConnState.$index = established",
            "TIMETRA-BGP-MIB::tBgpPeerNgShutdown.$index = 2",
            "TIMETRA-BGP-MIB::tBgpPeerNgOperLastEvent.$index = start",
            "TIMETRA-BGP-MIB::tBgpPeerFsmEstablishedTime.$index = 100",
            "TIMETRA-BGP-MIB::tBgpPeerNgOperMsgOctetsRcvd.$index = $received",
            "TIMETRA-BGP-MIB::tBgpPeerNgOperMsgOctetsSent.$index = $sent",
        ]) . "\n");
        $values = $response->valuesByIndex()[$index];
        $this->assertSame($received, $values['TIMETRA-BGP-MIB::tBgpPeerNgOperMsgOctetsRcvd']);
        $this->assertSame($sent, $values['TIMETRA-BGP-MIB::tBgpPeerNgOperMsgOctetsSent']);

        SnmpQuery::shouldReceive('enumStrings->numericIndex->abortOnFailure->walk')
            ->once()->with(['TIMETRA-BGP-MIB::tBgpPeerNgTable', 'TIMETRA-BGP-MIB::tBgpPeerNgOperTable'])
            ->andReturn($response);

        $datastore = Mockery::mock(DataStorageInterface::class);
        $datastore->shouldReceive('put')->once()->withArgs(function ($device, $measurement, $tags, $fields) use ($expectedReceived, $expectedSent) {
            $this->assertSame('bgp', $measurement);
            $this->assertSame($expectedReceived, $fields['bgpPeerInTotal']);
            $this->assertSame($expectedSent, $fields['bgpPeerOutTotal']);

            return true;
        });
        $this->app->instance('Datastore', $datastore);
        DeviceCache::setPrimary($device->device_id);
        $deviceArray = DeviceCache::getPrimary()->toArray();
        $os = OS::make($deviceArray);

        // Fail on any diagnostic, including PHP's lossy float-string to int deprecation.
        set_error_handler(static function (int $severity, string $message, string $file, int $line): never {
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });
        try {
            (new LegacyModule('bgp-peers'))->poll($os, $datastore);
        } finally {
            restore_error_handler();
        }

        $peer->refresh();
        $this->assertEquals($expectedReceived, $peer->bgpPeerInTotalMessages);
        $this->assertEquals($expectedSent, $peer->bgpPeerOutTotalMessages);
        DeviceCache::flush();
    }

    /**
     * @return array<string, array{string, string, int, int}>
     */
    public static function counter64Provider(): array
    {
        // Expected remainders calculated independently with arbitrary precision integer arithmetic.
        return [
            'zero and one' => ['0', '1', 0, 1],
            '16 bit carry' => ['65535', '65536', 65535, 65536],
            'above 16 bit carry' => ['65537', '131071', 65537, 131071],
            '32 bit values' => ['123456789', '2147483648', 123456789, 2147483648],
            '32 bit boundary' => ['4294967295', '4294967296', 4294967295, 0],
            'above 32 bit boundary' => ['4294967297', '8589934593', 1, 1],
            'signed 64 bit boundary' => ['9223372036854775807', '9223372036854775808', 4294967295, 0],
            'above signed 64 bit boundary' => ['9223372036854775809', '18446744073709551614', 1, 4294967294],
            'unsigned 64 bit maximum' => ['18446744073709551615', '18446744073709551615', 4294967295, 4294967295],
            'leading zeros' => ['0000004294967297', '000018446744073709551615', 1, 4294967295],
            'leading zero and one' => ['000000', '000001', 0, 1],
            'reported counters' => ['18446744071926153491', '18446744072817541484', 2511569171, 3402957164],
            'reported receive and send' => ['18446744072275573902', '18446744072631951242', 2860989582, 3217366922],
        ];
    }
}
