<?php

namespace LibreNMS\Tests\Feature\Jobs;

use App\Events\PollingModule;
use App\Facades\LibrenmsConfig;
use App\Jobs\PollDevice;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use LibreNMS\Data\Source\Icmp\Fping;
use LibreNMS\Data\Source\Icmp\FpingResponse;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Tests\DBTestCase;
use LibreNMS\Util\ModuleList;
use Mockery;

final class PollDeviceTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testOnlyEnabledModulesArePolled(): void
    {
        Event::fake([PollingModule::class]);
        LibrenmsConfig::set('rrd.enable', false);
        LibrenmsConfig::set('poller_modules', ['availability' => false]);
        $this->app->instance('Datastore', Mockery::mock(DataStorageInterface::class)->shouldIgnoreMissing());
        $fping = Mockery::mock(Fping::class);
        $fping->shouldReceive('ping')->andReturn(FpingResponse::artificialUp('192.0.2.1'));
        $this->app->instance(Fping::class, $fping);

        $device = Device::factory()->create(['hostname' => '192.0.2.1']);
        DevicePollingMethod::factory()->for($device)->create(['method_type' => PollingMethodType::Icmp]);

        (new PollDevice($device->device_id, ModuleList::fromUserOverrides([])))->handle();

        Event::assertDispatched(PollingModule::class, fn (PollingModule $event): bool => $event->module === 'core');
        Event::assertNotDispatched(PollingModule::class, fn (PollingModule $event): bool => $event->module === 'availability');

        $device->setAttrib('poll_availability', 1);
        (new PollDevice($device->device_id, ModuleList::fromUserOverrides([])))->handle();

        Event::assertDispatched(PollingModule::class, fn (PollingModule $event): bool => $event->module === 'availability');
    }
}
