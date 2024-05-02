<?php

namespace LibreNMS\Tests\Unit\Util;

use LibreNMS\Tests\TestCase;
use LibreNMS\Util\Number;

class NumberTest extends TestCase
{
    public function testToBytes(): void
    {
        $this->assertEquals(2147483648, Number::toBytes('2GiB'));
        $this->assertEquals(2147483648, Number::toBytes('2GiBytes'));
        $this->assertEquals(2147483648, Number::toBytes('2Gib'));
        $this->assertEquals(2000000000, Number::toBytes('2GB'));
        $this->assertEquals(2000000000, Number::toBytes('2 Gbps')); // match Number::formatSI() output
        $this->assertEquals(2000000000, Number::toBytes('2Gb'));
        $this->assertEquals(2000000000, Number::toBytes('2G'));
        $this->assertEquals(3145728, Number::toBytes('3MiB'));
        $this->assertEquals(3000000, Number::toBytes('3M'));
        $this->assertEquals(4398046511104, Number::toBytes('4TiB'));
        $this->assertEquals(4000000000000, Number::toBytes('4TB'));
        $this->assertEquals(5629499534213120, Number::toBytes('5PiB'));
        $this->assertEquals(5000000000000000, Number::toBytes('5PB'));
        $this->assertEquals(12000, Number::toBytes('12k'));
        $this->assertEquals(12000, Number::toBytes('12Kb'));
        $this->assertEquals(12288, Number::toBytes('12Ki'));
        $this->assertEquals(12288, Number::toBytes('12KiB'));
        $this->assertEquals(12288, Number::toBytes('12kiB')); // not technically valid, but allowed
        $this->assertEquals(12, Number::toBytes('12B'));
        $this->assertEquals(1234, Number::toBytes('1234'));
        $this->assertSame(0, (int) Number::toBytes('garbage')); // NAN cast to int is 0
        $this->assertNan(Number::toBytes('1m'));
        $this->assertNan(Number::toBytes('1234a'));
        $this->assertNan(Number::toBytes('1234as'));
        $this->assertNan(Number::toBytes('1234asd'));
        $this->assertNan(Number::toBytes('fluff'));
    }
}
