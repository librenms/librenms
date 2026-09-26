<?php

/*
 * NokiaEncapTest.php
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
use LibreNMS\Util\NokiaEncap;

final class NokiaEncapTest extends TestCase
{
    public function testNullEncapDecodes(): void
    {
        $this->assertSame(['outer' => 0, 'inner' => null], NokiaEncap::decode(0));
    }

    public function testDot1qEncapDecodes(): void
    {
        $this->assertSame(['outer' => 500, 'inner' => null], NokiaEncap::decode(500));
        $this->assertSame(['outer' => 1, 'inner' => null], NokiaEncap::decode(1));
    }

    public function testQinQEncapDecodes(): void
    {
        // outer 100, inner 200: inner VLAN lives in the upper 16 bits
        $encap = (200 << 16) | 100;
        $this->assertSame(['outer' => 100, 'inner' => 200], NokiaEncap::decode($encap));
    }

    public function testStringInputIsAccepted(): void
    {
        $this->assertSame(['outer' => 500, 'inner' => null], NokiaEncap::decode('500'));
    }

    public function testDot1qFormats(): void
    {
        $this->assertSame('500', NokiaEncap::format(500));
        $this->assertSame('0', NokiaEncap::format(0));
    }

    public function testQinQFormatsAsOuterDotInner(): void
    {
        $this->assertSame('100.200', NokiaEncap::format((200 << 16) | 100));
    }

    public function testWildcardFormats(): void
    {
        $this->assertSame('*', NokiaEncap::format(4095));
    }
}
