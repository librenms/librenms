<?php

/**
 * XklAutomaticOTDRTriggeredTest.php
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

final class XklAutomaticOTDRTriggeredTest extends SnmpTrapTestCase
{
    public function testXklAutomaticOTDRTriggered(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:4:53:43.00
SNMPv2-MIB::snmpTrapOID.0 XKL-MIB::xklAutomaticOTDRTriggered
XKL-MIB::xklOTDRLatestMeasurementOSCIndex.1 1
XKL-MIB::xklOTDRLatestMeasurementOSCDescr.1 OSC 0 (N\/A)
XKL-MIB::xklOTDRLatestMeasurementResults.1 47.91km
XKL-MIB::xklOTDRLatestMeasurementDateTime.1 06-23-2026 01:25:21
XKL-MIB::xklOTDRLatestMeasurementOTDRGroupNumber.1 none
XKL-MIB::xklOTDRLatestMeasurementOTDRGroupName.1 N\/A
TRAP,

            'Automatic OTDR Event at 06-23-2026 01:25:21. Name: OSC 0 (N\/A) Length: 47.91km',
            'Failed to handle XklAutomaticOTDRTriggered trap.',
            [Severity::Warning],
        );
    }
}
