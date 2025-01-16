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

    public function testParseAtTime(): void
    {
        $this->assertEquals(time(), Time::parseAt('now'), 'now did not match');
        $this->assertEquals(time() + 180, Time::parseAt('+3m'), '+3m did not match');
        $this->assertEquals(time() + 7200, Time::parseAt('+2h'), '+2h did not match');
        $this->assertEquals(time() + 172800, Time::parseAt('+2d'), '+2d did not match');
        $this->assertEquals(time() + 63115200, Time::parseAt('+2y'), '+2y did not match');
        $this->assertEquals(time() - 180, Time::parseAt('-3m'), '-3m did not match');
        $this->assertEquals(time() - 7200, Time::parseAt('-2h'), '-2h did not match');
        $this->assertEquals(time() - 172800, Time::parseAt('-2d'), '-2d did not match');
        $this->assertEquals(time() - 63115200, Time::parseAt('-2y'), '-2y did not match');
        $this->assertEquals(429929439, Time::parseAt('429929439'));
        $this->assertEquals(212334234, Time::parseAt(212334234));
        $this->assertEquals(time() - 43, Time::parseAt('-43'), '-43 did not match');
        $this->assertEquals(0, Time::parseAt('invalid'));
        $this->assertEquals(606614400, Time::parseAt('March 23 1989 UTC'));
        $this->assertEquals(time() + 86400, Time::parseAt('+1 day'));
    }
}
