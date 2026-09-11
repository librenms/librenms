<?php

/**
 * XklTransportLinkTest.php
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
 * @copyright  2026 Heath Barnhart
 * @author     Heath Barnhart hbarnhart@kanren.net
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use LibreNMS\Enum\Severity;

final class XklTransportLinkTest extends SnmpTrapTestCase
{
    public function testXklTransportLinkUp(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:4:53:43.00
SNMPv2-MIB::snmpTrapOID.0 XKL-MIB::xklTransportLinkUp
XKL-MIB::xklTransportIndex.1 1
XKL-MIB::xklTransportStatus.1 up
XKL-MIB::xklTransportTxStatus.1 up
XKL-MIB::xklTransportRxStatus.1 up
TRAP,

            'Tranport 1 is up.',
            'Failed to handle XklTransportLinkUp trap',
            [Severity::Ok],
        );
    }

    public function testXklTransportLinkDown(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:4:53:43.00
SNMPv2-MIB::snmpTrapOID.0 XKL-MIB::xklTransportLinkDown
XKL-MIB::xklTransportIndex.3 3
XKL-MIB::xklTransportStatus.3 down
XKL-MIB::xklTransportTxStatus.3 up
XKL-MIB::xklTransportRxStatus.3 los
TRAP,

            'Transport service 3 is down. Transmit status: up Receive status: los',
            'Failed to handle trap XklTransportLinkDown',
            [Severity::Error],
        );
    }
}
