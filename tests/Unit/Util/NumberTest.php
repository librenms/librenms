<?php

/**
 * NumberTest.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link       http://librenms.org
 *
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Tests\Unit\Util;

use InvalidArgumentException;
use LibreNMS\Exceptions\InsufficientDataException;
use LibreNMS\Tests\TestCase;
use LibreNMS\Util\Number;

final class NumberTest extends TestCase
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

    public function testPercentCalculation(): void
    {
        $this->assertEquals(99, Number::calculatePercent(99, 100));
        $this->assertEquals(0.03, Number::calculatePercent(345, 1023450));
        $this->assertEquals(0.0337, Number::calculatePercent(345, 1023450, 4));
        $this->assertEquals(0, Number::calculatePercent(-1, 43));
        $this->assertEquals(0, Number::calculatePercent(-1, -43));
        $this->assertEquals(0, Number::calculatePercent(43, -43));
        $this->assertEquals(29394.26, Number::calculatePercent(12639.53, 43));
    }

    public function testFillMissingRatio(): void
    {
        $this->assertEquals([20, 10, 10, 50], Number::fillMissingRatio(total: 20, used: 10));
        $this->assertEquals([23, 9, 14, 39.13], Number::fillMissingRatio(total: 23, available: 14));
        $this->assertEquals([51, 15, 36, 29.41], Number::fillMissingRatio(used: 15, available: 36));
        $this->assertEquals([70, 9.8, 60.2, 14], Number::fillMissingRatio(total: 70, used_percent: 14.0));
        $this->assertEquals([300, 66, 234, 22], Number::fillMissingRatio(used: 66, used_percent: 22.0));
        $this->assertEquals([169.065, 75.065, 94, 44.4], Number::fillMissingRatio(available: 94, used_percent: 44.4, precision: 3));
        $this->assertEquals([100, 10, 90, 10.0], Number::fillMissingRatio(used_percent: 10));

        // out of bounds percent
        $this->assertEquals([100, 99.05, 0.95, 99.05], Number::fillMissingRatio(used_percent: 9905));

        // precision
        $this->assertEquals([100, 99, 1, 99], Number::fillMissingRatio(used_percent: 9905, precision: 0));

        // multiplier and large numbers
        $this->assertEquals([12637445438703855616, 1559460767136055808, 11077984671567799808, 12], Number::fillMissingRatio(total: 12341255311234234, used_percent: 12.34, precision: 0, multiplier: 1024));

        // handle strings
        $this->assertEquals([20, 10, 10, 50], Number::fillMissingRatio(total: '20', used_percent: '50'));

        try {
            Number::fillMissingRatio();
            $this->fail('No exception thrown');
        } catch (InsufficientDataException) {
        }

        try {
            Number::fillMissingRatio(total: 1);
            $this->fail('No exception thrown');
        } catch (InsufficientDataException) {
        }

        try {
            Number::fillMissingRatio(used: 1);
            $this->fail('No exception thrown');
        } catch (InsufficientDataException) {
        }

        try {
            Number::fillMissingRatio(available: 1);
            $this->fail('No exception thrown');
        } catch (InsufficientDataException) {
        }
    }

    public function testCalculateRate(): void
    {
        $this->assertEquals(100.0, Number::calculateRate('100', '200', 1.0, 2.0));
        $this->assertEquals(101.0, Number::calculateRate('4294967295', '100', 1.0, 2.0));
        $this->assertEquals(250.5, Number::calculateRate('18446744073709551615', '500', 1.0, 3.0, 64));
        $this->assertEquals(0.0, Number::calculateRate('1000', '1000', 1.0, 2.0));
        $this->assertEquals(100.0, Number::calculateRate('100', '200', 1.0, 2.0)); //32bit
        $this->assertEquals(100.0, Number::calculateRate('5000000000', '5000000100', 1.0, 2.0)); //64bit
        $this->assertEquals(200.0, Number::calculateRate('100', '300', 1.5, 2.5)); // 200 difference / 1.0 second
    }

    public function testCalculateRateInvalidTimeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Current time must be greater than previous time');
        Number::calculateRate('100', '200', 2.0, 1.0);
    }

    public function testCalculateRateEqualTimeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Number::calculateRate('100', '200', 1.0, 1.0);
    }

    public function testCalculateRateNegativeValueThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Counter values must be non-negative integers');
        Number::calculateRate('-100', '200', 1.0, 2.0);
    }

    public function testCalculateRateNonNumericValueThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Number::calculateRate('abc', '200', 1.0, 2.0);
    }
}
