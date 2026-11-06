<?php

namespace LibreNMS\Tests\Feature;

use App\Models\Device;
use App\Models\DevicePollingMethod;
use Illuminate\Support\Facades\DB;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Tests\InMemoryDbTestCase;

final class DevicePollingMethodTest extends InMemoryDbTestCase
{
    public function testCheckResultIsOnlyWrittenWhenItChanges(): void
    {
        $this->travelTo('2026-10-05 10:00:00');
        $method = DevicePollingMethod::factory()->create([
            'device_id' => Device::factory()->create()->device_id,
            'method_type' => PollingMethodType::Icmp,
            'last_check_successful' => true,
        ]);
        $this->assertEquals('2026-10-05 10:00:00', $method->last_check_changed_at);
        $method = $method->fresh(); // loaded like a ping or poll does

        // the same result again is not written
        $this->travelTo('2026-10-05 10:05:00');
        DB::enableQueryLog();
        $method->last_check_successful = true;
        $method->last_check_message = null;
        $method->save();
        $this->assertSame([], DB::getQueryLog());
        $this->assertEquals('2026-10-05 10:00:00', $method->fresh()->last_check_changed_at);

        // a new result is written with the time it changed
        $this->travelTo('2026-10-05 10:10:00');
        $method->last_check_successful = false;
        $method->last_check_message = 'host : xmt/rcv/%loss = 3/0/100%';
        $method->save();
        $this->assertEquals('2026-10-05 10:10:00', $method->fresh()->last_check_changed_at);

        // a message change alone does not move the time
        $this->travelTo('2026-10-05 10:15:00');
        $method->last_check_message = 'Timeout';
        $method->save();
        $this->assertEquals('2026-10-05 10:10:00', $method->fresh()->last_check_changed_at);
    }
}
