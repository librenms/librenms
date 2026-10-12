<?php

namespace LibreNMS\Tests\Feature\Jobs;

use App\Actions\Device\CheckDeviceAvailability;
use App\Jobs\DiscoverDevice;
use App\Jobs\DispatchPollingWork;
use App\Models\Device;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;
use LibreNMS\Util\ModuleList;
use RuntimeException;

final class DiscoverDeviceTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testFailedQueuedDiscoveryIsDeferred(): void
    {
        $this->freezeSecond();
        $device = Device::factory()->create(['hostname' => '192.0.2.1', 'status' => 1, 'last_discovered' => null]);
        $this->app->instance(CheckDeviceAvailability::class, new class
        {
            public function execute(): never
            {
                throw new RuntimeException('discovery failed');
            }
        });

        try {
            DiscoverDevice::dispatch($device->device_id, ModuleList::fromUserOverrides([]))->onConnection('sync');
            $this->fail('Discovery should have failed');
        } catch (RuntimeException $e) {
            $this->assertSame('discovery failed', $e->getMessage());
        }

        $device->refresh();
        $this->assertNull($device->last_discovered, 'a failed discovery is not complete, the device should not be polled');
        $this->assertEquals(now()->addSeconds(DispatchPollingWork::discoveryInterval()), $device->discovery_deferred_until, 'a failed discovery should wait for the next discovery interval');
    }

    public function testDownDeviceIsDeferredAndNotMarkedAsDiscovered(): void
    {
        $this->freezeSecond();
        $device = Device::factory()->create(['hostname' => '192.0.2.1', 'status' => 0, 'last_discovered' => null]);
        $this->fakeAvailability(false);

        (new DiscoverDevice($device->device_id, ModuleList::fromUserOverrides([])))->handle();

        $device->refresh();
        $this->assertNull($device->last_discovered, 'a down device should not be marked as discovered');
        $this->assertEquals(now()->addSeconds(DispatchPollingWork::pollInterval()), $device->discovery_deferred_until, 'a down device should be tried again next poll interval');
    }

    public function testCompletedDiscoveryClearsDeferral(): void
    {
        $this->freezeSecond();
        $device = Device::factory()->create([
            'hostname' => '192.0.2.1',
            'status' => 1,
            'snmp_disable' => 1,
            'last_discovered' => null,
            'discovery_deferred_until' => now()->subMinute(),
        ]);
        $this->fakeAvailability(true);

        (new DiscoverDevice($device->device_id, ModuleList::fromUserOverrides(['availability'])))->handle();

        $device->refresh();
        $this->assertEquals(now(), $device->last_discovered);
        $this->assertNotNull($device->last_discovered_timetaken);
        $this->assertNull($device->discovery_deferred_until);
    }

    private function fakeAvailability(bool $up): void
    {
        $this->app->instance(CheckDeviceAvailability::class, new class($up)
        {
            public function __construct(private readonly bool $up)
            {
            }

            public function execute(Device $device): bool
            {
                $device->status = $this->up;
                $device->status_reason = $this->up ? '' : 'icmp';

                return $this->up;
            }
        });
    }
}
