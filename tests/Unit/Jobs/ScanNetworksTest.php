<?php

namespace LibreNMS\Tests\Unit\Jobs;

use App\Actions\Device\AddFoundDeviceResult;
use App\Events\NetworkAddressScanned;
use App\Facades\LibrenmsConfig;
use App\Jobs\ScanNetworks;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use LibreNMS\Enum\DeviceAddOutcome;
use LibreNMS\Scan\NetworkScanner;
use LibreNMS\Scan\ScanOptions;
use LibreNMS\Tests\TestCase;
use LibreNMS\Util\IP;
use Mockery;
use PHPUnit\Framework\Attributes\TestDox;

final class ScanNetworksTest extends TestCase
{
    #[TestDox('Scans the nets setting and reports every address with the scan id')]
    public function testReportsResults(): void
    {
        Event::fake([NetworkAddressScanned::class]);
        LibrenmsConfig::set('nets', ['192.0.2.0/30']);
        LibrenmsConfig::set('autodiscovery.nets-exclude', ['198.51.100.0/24', 'invalid']);
        LibrenmsConfig::set('snmp.default_credentials', []);

        $scanner = Mockery::mock(NetworkScanner::class);
        $scanner->shouldReceive('scan')->once()->withArgs(function (array $networks, ScanOptions $options, callable $onResult): bool {
            $this->assertEquals([IP::parse('192.0.2.0/30')], $networks);
            $this->assertSame(['198.51.100.0/24'], $options->excludedNetworks);
            $this->assertSame(3, $options->pollerGroup);
            $this->assertTrue($options->pingFallback);

            $onResult('192.0.2.1', new AddFoundDeviceResult(DeviceAddOutcome::Unreachable));
            $onResult('192.0.2.2', new AddFoundDeviceResult(DeviceAddOutcome::Known, 'known'));

            return true;
        });
        $this->app->instance(NetworkScanner::class, $scanner);

        Bus::dispatchSync(new ScanNetworks('scan-1', pollerGroup: 3, pingFallback: true));

        Event::assertDispatchedTimes(NetworkAddressScanned::class, 2);
        Event::assertDispatched(NetworkAddressScanned::class, fn (NetworkAddressScanned $event) => $event->scanId === 'scan-1'
            && $event->ip === '192.0.2.2'
            && $event->result->outcome === DeviceAddOutcome::Known);
    }

    #[TestDox('Runs on a poller of the poller group the devices are added to')]
    public function testQueue(): void
    {
        $this->assertSame('discovery', (new ScanNetworks('scan'))->queue);
        $this->assertSame('discovery-2', (new ScanNetworks('scan', pollerGroup: 2))->queue);
    }

    #[TestDox('Only plain values are queued, not the SNMP credentials')]
    public function testSerializable(): void
    {
        $job = unserialize(serialize(new ScanNetworks('scan', ['192.0.2.0/24'], pingFallback: true)));

        $this->assertSame(['192.0.2.0/24'], $job->networks);
        $this->assertTrue($job->pingFallback);
    }
}
