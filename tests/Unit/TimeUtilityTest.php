<?php

namespace Tests\Unit;

use Illuminate\Support\Carbon;
use LibreNMS\Tests\TestCase;
use LibreNMS\Util\Time;

class TimeUtilityTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function testFormatInterval(): void
    {
        $this->assertSame('', Time::formatInterval(0));
        $this->assertSame('', Time::formatInterval(null));
        $this->assertSame('1 second', Time::formatInterval(1));
        $this->assertSame('3s', Time::formatInterval(3, true));
        $this->assertSame('1 minute', Time::formatInterval(60));
        $this->assertSame('1 minute ago', Time::formatInterval(-60));
        $this->assertSame('1m 1s', Time::formatInterval(61, true));
        $this->assertSame('1 hour', Time::formatInterval(60 * 60));
        $this->assertSame('1 day', Time::formatInterval(24 * 60 * 60));
        $this->assertSame('2 weeks 3 days 24 minutes 16 seconds', Time::formatInterval(17 * 24 * 60 * 60 + 1456));
        $this->assertSame('2 weeks 3 days', Time::formatInterval(17 * 24 * 60 * 60 + 1456, parts: 2));

        // different months could change this
        $this->travelTo(Carbon::createFromTimestamp(30042), function () {
            $this->assertSame('1 month 1 week 2 days 24 minutes', Time::formatInterval(39 * 24 * 60 * 60 + 1456));
            $this->assertSame('1mo 1w 2d 24m 16s', Time::formatInterval(39 * 24 * 60 * 60 + 1456, true, 5));
        });

        // calculate if there is a leap year (could freeze time, try this instead)
        if (Carbon::createFromDate(Carbon::now()->year, 2, 28)->isPast()) {
            $days = Carbon::now()->isLeapYear() ? 366 : 365;
        } else {
            $days = Carbon::now()->subYear()->isLeapYear() ? 366 : 365;
        }

        $this->assertSame('1 year', Time::formatInterval($days * 24 * 60 * 60));
        $this->assertSame('1 year ago', Time::formatInterval(-$days * 24 * 60 * 60));

        $this->assertSame('4 years', Time::formatInterval(1461 * 24 * 60 * 60));
    }
}
