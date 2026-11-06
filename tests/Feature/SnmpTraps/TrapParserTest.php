<?php

/**
 * TrapParserTest.php
 *
 * -Description-
 *
 * Tests how raw trap text is split into varbinds.
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
 *
 * @copyright  2026 LibreNMS contributors
 * @author     Andrei-Alexandru Bleortu
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use LibreNMS\Snmptrap\Trap;
use LibreNMS\Tests\TestCase;

final class TrapParserTest extends TestCase
{
    public function testQuotedValueSpanningLines(): void
    {
        $trap = new Trap("host\nUDP: [192.168.5.6]:162->[192.168.5.5]:162\n"
            . "SNMPv2-MIB::snmpTrapOID.0 FOUNDRY-SN-NOTIFICATION-MIB::snTrapStackingUnitAdded\n"
            . "FOUNDRY-SN-AGENT-MIB::snAgGblTrapMessage \"Stack: Stack unit 2 has been added\n\"\n"
            . "FOUNDRY-SN-AGENT-MIB::snChasUnitIndex 2\n");

        $this->assertSame('Stack: Stack unit 2 has been added', $trap->getOidData('FOUNDRY-SN-AGENT-MIB::snAgGblTrapMessage'));
        $this->assertSame('2', $trap->getOidData('FOUNDRY-SN-AGENT-MIB::snChasUnitIndex'));
    }

    public function testClosingQuoteWithTrailingWhitespace(): void
    {
        $trap = new Trap("host\nUDP: [192.168.5.6]:162->[192.168.5.5]:162\n"
            . "SNMPv2-MIB::sysDescr.0 \"message\n"
            . "\" \r\n"
            . "SNMPv2-MIB::sysName.0 sw1\n");

        $this->assertSame('message', $trap->getOidData('SNMPv2-MIB::sysDescr.0'));
        $this->assertSame('sw1', $trap->getOidData('SNMPv2-MIB::sysName.0'));
    }

    public function testQuotedValueKeepsItsInnerLines(): void
    {
        $trap = new Trap("host\nUDP: [192.168.5.6]:162->[192.168.5.5]:162\n"
            . "SNMPv2-MIB::sysDescr.0 \"first\nsecond\"\n"
            . "SNMPv2-MIB::sysName.0 sw1\n");

        $this->assertSame("first\nsecond", $trap->getOidData('SNMPv2-MIB::sysDescr.0'));
        $this->assertSame('sw1', $trap->getOidData('SNMPv2-MIB::sysName.0'));
    }

    public function testUnclosedQuoteStopsAtTheNextVarbind(): void
    {
        $trap = new Trap("host\nUDP: [192.168.5.6]:162->[192.168.5.5]:162\n"
            . "SNMPv2-MIB::sysDescr.0 \"no closing quote\n"
            . "SNMPv2-MIB::sysName.0 sw1\n"
            . ".1.3.6.1.2.1.1.6.0 roof\n");

        $this->assertSame('no closing quote', $trap->getOidData('SNMPv2-MIB::sysDescr.0'));
        $this->assertSame('sw1', $trap->getOidData('SNMPv2-MIB::sysName.0'));
        $this->assertSame('roof', $trap->getOidData('.1.3.6.1.2.1.1.6.0'));
    }

    public function testEscapedQuoteDoesNotCloseAValue(): void
    {
        $trap = new Trap("host\nUDP: [192.168.5.6]:162->[192.168.5.5]:162\n"
            . "SNMPv2-MIB::sysDescr.0 \"Port \\\"uplink\\\"\n"
            . "down\"\n"
            . "SNMPv2-MIB::sysName.0 sw1\n");

        $this->assertSame("Port \\\"uplink\\\"\ndown", $trap->getOidData('SNMPv2-MIB::sysDescr.0'));
        $this->assertSame('sw1', $trap->getOidData('SNMPv2-MIB::sysName.0'));
    }

    public function testLoneQuoteOpensAValue(): void
    {
        $trap = new Trap("host\nUDP: [192.168.5.6]:162->[192.168.5.5]:162\n"
            . "SNMPv2-MIB::sysDescr.0 \"\n"
            . "text\n"
            . "\"\n"
            . "SNMPv2-MIB::sysName.0 sw1\n");

        $this->assertSame("\ntext", $trap->getOidData('SNMPv2-MIB::sysDescr.0'));
        $this->assertSame('sw1', $trap->getOidData('SNMPv2-MIB::sysName.0'));
    }
}
