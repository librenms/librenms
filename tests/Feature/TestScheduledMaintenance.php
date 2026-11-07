<?php

namespace LibreNMS\Tests\Feature;

use App\Models\AlertSchedule;
use App\Models\Device;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use LibreNMS\Enum\AlertScheduleStatus;
use LibreNMS\Tests\DBTestCase;

final class TestScheduledMaintenance extends DBTestCase
{
    private $timezone;

    public function testNormal(): void
    {
        $now = CarbonImmutable::now();

        $schedule = AlertSchedule::factory()->make(); /** @var AlertSchedule $schedule */
        $schedule->start = $now->subHour();
        $schedule->end = $now->addHour();
        $schedule->save();

        $this->setTimezone('UTC');
        $this->assertScheduleActive($now, $schedule);
        $this->assertScheduleLapsed($now->addHours(2), $schedule);
        $this->assertScheduleLapsed($now->addDays(10), $schedule);
        $this->assertScheduleSet($now->subHours(2), $schedule);
        $this->assertScheduleSet($now->subDays(10), $schedule);

        $this->setTimezone('America/New_York');
        $schedule = $schedule->fresh();
        $this->assertScheduleActive($now, $schedule);
        $this->assertScheduleLapsed($now->addHours(2), $schedule);
        $this->assertScheduleSet($now->subHours(2), $schedule);
    }

    public function testRecurringNormal(): void
    {
        $this->setTimezone('America/New_York');
        $schedule = AlertSchedule::factory()->recurring()->make();
        $schedule->recurring_day = '1,2,3,4,5';
        $schedule->start = Carbon::parse('2020-09-10 2:00');
        $schedule->end = Carbon::parse('9000-09-09 20:00');
        $schedule->save();

        $this->assertScheduleActive(Carbon::parse('2020-09-10 2:01'), $schedule);
        $this->assertScheduleActive(Carbon::parse('2020-09-10 2:00'), $schedule);
        $this->assertScheduleSet(Carbon::parse('2020-09-10 1:59'), $schedule);
        $this->assertScheduleActive(Carbon::parse('2020-09-10 19:59'), $schedule);
        $this->assertScheduleSet(Carbon::parse('2020-09-10 20:01'), $schedule);
        $this->assertScheduleSet(Carbon::parse('2020-09-11 01:00'), $schedule);
        $this->assertScheduleActive(Carbon::parse('2020-09-11 11:00'), $schedule);
        $this->assertScheduleSet(Carbon::parse('2020-09-12 11:00'), $schedule);
        $this->assertScheduleActive(Carbon::parse('2020-09-14 10:00'), $schedule);

        $this->assertScheduleLapsed(Carbon::parse('9999-09-09 20:00'), $schedule);
    }

    public function testRecurringUsesScheduleTimezoneNotServerTimezone(): void
    {
        $this->setTimezone('UTC');
        $schedule = AlertSchedule::factory()->recurring()->make(['timezone' => 'Asia/Tokyo']);
        $schedule->recurring_day = '1'; // Monday
        $schedule->start = '2026-09-01 08:00';
        $schedule->end = '9000-09-09 10:00';
        $schedule->save();

        $this->assertSame('2026-08-31 23:00:00', $schedule->getAttributes()['start']);
        $this->assertScheduleActive(Carbon::parse('2026-10-04 23:30', 'UTC'), $schedule); // Monday 08:30 in Tokyo
        $this->assertScheduleSet(Carbon::parse('2026-10-05 23:30', 'UTC'), $schedule); // Tuesday 08:30 in Tokyo
        $this->assertScheduleSet(Carbon::parse('2026-10-05 08:30', 'UTC'), $schedule); // Monday 17:30 in Tokyo
    }

    public function testRecurringFollowsLocalTimeAcrossDst(): void
    {
        $this->setTimezone('UTC');
        $schedule = AlertSchedule::factory()->recurring()->make(['timezone' => 'America/New_York']);
        $schedule->start = '2026-01-05 09:00'; // standard time
        $schedule->end = '9000-09-09 17:00'; // daylight time
        $schedule->save();

        $this->assertScheduleActive(Carbon::parse('2027-01-11 09:30', 'America/New_York'), $schedule);
        $this->assertScheduleSet(Carbon::parse('2027-01-11 08:30', 'America/New_York'), $schedule);
        $this->assertScheduleActive(Carbon::parse('2026-07-06 09:30', 'America/New_York'), $schedule);
        $this->assertScheduleSet(Carbon::parse('2026-07-06 08:30', 'America/New_York'), $schedule);
        $this->assertScheduleActive(Carbon::parse('2026-07-06 16:30', 'America/New_York'), $schedule);
        $this->assertScheduleSet(Carbon::parse('2026-07-06 17:30', 'America/New_York'), $schedule);
        // the day the clocks change
        $this->assertScheduleActive(Carbon::parse('2026-03-08 09:30', 'America/New_York'), $schedule);
        $this->assertScheduleSet(Carbon::parse('2026-03-08 08:30', 'America/New_York'), $schedule);
    }

    public function testRecurringSpanningMidnightInScheduleTimezone(): void
    {
        $this->setTimezone('America/New_York');
        $schedule = AlertSchedule::factory()->recurring()->make(['timezone' => 'Europe/Berlin']);
        $schedule->start = '2026-07-01 23:00';
        $schedule->end = '9000-09-09 01:00';
        $schedule->save();

        $this->assertScheduleActive(Carbon::parse('2026-07-10 23:30', 'Europe/Berlin'), $schedule);
        $this->assertScheduleActive(Carbon::parse('2026-07-11 00:30', 'Europe/Berlin'), $schedule);
        $this->assertScheduleSet(Carbon::parse('2026-07-11 01:30', 'Europe/Berlin'), $schedule);
        $this->assertScheduleSet(Carbon::parse('2026-07-11 22:30', 'Europe/Berlin'), $schedule);
    }

    public function testActiveRecurringScheduleFoundThroughDeviceRelation(): void
    {
        $this->setTimezone('UTC');
        $device = Device::factory()->create();
        $schedule = AlertSchedule::factory()->recurring()->make(['timezone' => 'Asia/Tokyo']);
        $schedule->start = '2026-09-01 08:00';
        $schedule->end = '9000-09-09 10:00';
        $schedule->save();
        $schedule->devices()->attach($device);

        $this->setTestNow(Carbon::parse('2026-10-05 09:00', 'Asia/Tokyo'));
        $this->assertTrue($device->alertSchedules()->isActive()->exists());

        $this->setTestNow(Carbon::parse('2026-10-05 11:00', 'Asia/Tokyo'));
        $this->assertFalse($device->alertSchedules()->isActive()->exists());
    }

    public function testNewScheduleDefaultsToServerTimezone(): void
    {
        $this->setTimezone('America/New_York');

        $schedule = new AlertSchedule;
        $schedule->start = '2026-07-01 12:00';

        $this->assertSame('America/New_York', $schedule->timezone);
        $this->assertSame('2026-07-01 16:00:00', $schedule->getAttributes()['start']);
    }

    private function assertScheduleActive($time, $schedule)
    {
        $this->setTestNow($time);
        $this->assertEquals(AlertScheduleStatus::ACTIVE, $schedule->status, "$schedule is not active at $time (code)");
        $this->assertTrue(AlertSchedule::where('schedule_id', $schedule->schedule_id)->isActive()->exists(), "$schedule is not active at $time (sql)");
    }

    private function assertScheduleSet($time, $schedule)
    {
        $this->setTestNow($time);
        $this->assertEquals(AlertScheduleStatus::SET, $schedule->status, "$schedule is not set at $time (code)");
        $this->assertFalse(AlertSchedule::where('schedule_id', $schedule->schedule_id)->isActive()->exists(), "$schedule is not set at $time (sql)");
    }

    private function assertScheduleLapsed($time, $schedule)
    {
        $this->setTestNow($time);
        $this->assertEquals(AlertScheduleStatus::LAPSED, $schedule->status, "$schedule is not lapsed at $time (code)");
        $this->assertFalse(AlertSchedule::where('schedule_id', $schedule->schedule_id)->isActive()->exists(), "$schedule is not lapsed at $time (sql)");
    }

    /**
     * Set the test time
     *
     * @param  Carbon|CarbonImmutable  $time
     */
    private function setTestNow($time)
    {
        Carbon::setTestNow($time);
        CarbonImmutable::setTestNow($time);
    }

    private function setTimezone($timezone)
    {
        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->timezone = config('app.timezone');  //save timezone
    }

    protected function tearDown(): void
    {
        // revert temp time and timezone
        $this->setTimezone($this->timezone);
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }
}
