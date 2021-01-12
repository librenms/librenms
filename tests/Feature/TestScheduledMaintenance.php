<?php

namespace LibreNMS\Tests\Feature;

use App\Models\AlertSchedule;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use LibreNMS\Enum\AlertScheduleStatus;
use LibreNMS\Tests\DBTestCase;

class TestScheduledMaintenance extends DBTestCase
{
    private $timezone;

    public function testNormal()
    {
        $now = CarbonImmutable::now();

        $schedule = AlertSchedule::factory()->make();
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

    public function testRecurringNormal()
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
//        $this->assertScheduleSet(Carbon::parse('2020-09-10 20:01'), $schedule); // FIXME broken since end is 1am UTC
//        $this->assertScheduleSet(Carbon::parse('2020-09-11 01:00'), $schedule);
        $this->assertScheduleActive(Carbon::parse('2020-09-11 11:00'), $schedule);
        $this->assertScheduleSet(Carbon::parse('2020-09-12 11:00'), $schedule);
        $this->assertScheduleActive(Carbon::parse('2020-09-14 10:00'), $schedule);

        $this->assertScheduleLapsed(Carbon::parse('9999-09-09 20:00'), $schedule);
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
     * @param Carbon|CarbonImmutable $time
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
