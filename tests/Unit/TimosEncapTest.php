<?php

/*
 * TimosEncapTest.php
 *
 * Tests decoding and formatting of TIMETRA-TC-MIB::TmnxEncapVal values.
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 */

namespace LibreNMS\Tests\Unit;

use LibreNMS\Tests\TestCase;
use LibreNMS\OS\Timos;

final class TimosEncapTest extends TestCase
{
    public function testNullEncapDecodes(): void
    {
        $this->assertSame(['outer' => 0, 'inner' => null], Timos::decodeEncapValue(0));
    }

    public function testDot1qEncapDecodes(): void
    {
        $this->assertSame(['outer' => 500, 'inner' => null], Timos::decodeEncapValue(500));
        $this->assertSame(['outer' => 1, 'inner' => null], Timos::decodeEncapValue(1));
    }

    public function testQinQEncapDecodes(): void
    {
        // outer 100, inner 200: inner VLAN lives in the upper 16 bits
        $encap = (200 << 16) | 100;
        $this->assertSame(['outer' => 100, 'inner' => 200], Timos::decodeEncapValue($encap));
    }

    public function testStringInputIsAccepted(): void
    {
        $this->assertSame(['outer' => 500, 'inner' => null], Timos::decodeEncapValue('500'));
    }

    public function testDot1qFormats(): void
    {
        $this->assertSame('500', Timos::formatEncapValue(500));
        $this->assertSame('0', Timos::formatEncapValue(0));
    }

    public function testQinQFormatsAsOuterDotInner(): void
    {
        $this->assertSame('100.200', Timos::formatEncapValue((200 << 16) | 100));
    }

    public function testWildcardFormats(): void
    {
        $this->assertSame('*', Timos::formatEncapValue(4095));
    }
}
