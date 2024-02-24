<?php
/**
 * AdvaAccThreholdCrossingAlertTest.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2019 Heath Barnhart
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

class AdvaAccThresholdCrossingAlertTest extends SnmpTrapTestCase
{
    public function testAccThresholdTrap(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-PERFORMANCE-MIB::cmEthernetAccPortThresholdCrossingAlert
CM-PERFORMANCE-MIB::cmEthernetAccPortThresholdIndex.1.1.1.2.1.37 37
CM-PERFORMANCE-MIB::cmEthernetAccPortThresholdInterval.1.1.1.2.1.37 interval-15min
CM-PERFORMANCE-MIB::cmEthernetAccPortThresholdVariable.1.1.1.2.1.37 CM-PERFORMANCE-MIB::cmEthernetAccPortStatsUAS.1.1.1.2.1
CM-PERFORMANCE-MIB::cmEthernetAccPortThresholdValueLo.1.1.1.2.1.37 10
CM-PERFORMANCE-MIB::cmEthernetAccPortThresholdValueHi.1.1.1.2.1.37 0
CM-PERFORMANCE-MIB::cmEthernetAccPortThresholdMonValue.1.1.1.2.1.37 10
IF-MIB::ifName.2 Access PORT-1-1-1-2
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 0B 2D 0A 00 2D 06 00 "
ADVA-MIB::neEventLogIndex.79 79
ADVA-MIB::neEventLogTimeStamp.79 2018-12-10,11:45:10.8,-6:0
TRAP,
            'Access PORT-1-1-1-2 unavailable seconds threshold exceeded for interval-15min',
            'Could not handle cmEthernetAccPortThresholdCrossingAlert UAS',
        );

        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-PERFORMANCE-MIB::cmEthernetAccPortThresholdCrossingAlert
CM-PERFORMANCE-MIB::cmEthernetAccPortThresholdIndex.1.1.1.2.1.37 37
CM-PERFORMANCE-MIB::cmEthernetAccPortThresholdInterval.1.1.1.2.1.37 interval-1day
CM-PERFORMANCE-MIB::cmEthernetAccPortThresholdVariable.1.1.1.2.1.37 CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESBP.1.1.1.2.1
CM-PERFORMANCE-MIB::cmEthernetAccPortThresholdValueLo.1.1.1.2.1.37 20
CM-PERFORMANCE-MIB::cmEthernetAccPortThresholdValueHi.1.1.1.2.1.37 0
CM-PERFORMANCE-MIB::cmEthernetAccPortThresholdMonValue.1.1.1.2.1.37 20
IF-MIB::ifName.2 Access PORT-1-1-1-2
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 0B 2D 0A 00 2D 06 00 "
ADVA-MIB::neEventLogIndex.79 79
ADVA-MIB::neEventLogTimeStamp.79 2018-12-10,11:45:10.8,-6:0
TRAP,
            'Access PORT-1-1-1-2 broadcast frames received threshold exceeded for interval-1day',
            'Could not handle cmEthernetAccPortThresholdCrossingAlert broadcast frame sent',
        );

        $this->assertTrapLogsMessage('{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-PERFORMANCE-MIB::cmEthernetAccPortThresholdCrossingAlert
CM-PERFORMANCE-MIB::cmEthernetAccPortThresholdIndex.1.1.1.3.1.37 37
CM-PERFORMANCE-MIB::cmEthernetAccPortThresholdInterval.1.1.1.3.1.37 interval-1day
CM-PERFORMANCE-MIB::cmEthernetAccPortThresholdVariable.1.1.1.3.1.37 CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESUP.1.1.1.2.1
CM-PERFORMANCE-MIB::cmEthernetAccPortThresholdValueLo.1.1.1.3.1.37 20
CM-PERFORMANCE-MIB::cmEthernetAccPortThresholdValueHi.1.1.1.3.1.37 0
CM-PERFORMANCE-MIB::cmEthernetAccPortThresholdMonValue.1.1.1.3.1.37 20
IF-MIB::ifName.2 Access PORT-1-1-1-3
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 0B 2D 0A 00 2D 06 00 "
ADVA-MIB::neEventLogIndex.79 79
ADVA-MIB::neEventLogTimeStamp.79 2018-12-10,11:45:10.8,-6:0',
            'Access PORT-1-1-1-3 unicast frames received threshold exceeded for interval-1day',
            'Could not handle cmEthernetAccPortThresholdCrossingAlert unicast frames sent',
        );

        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-PERFORMANCE-MIB::cmEthernetAccPortThresholdCrossingAlert
CM-PERFORMANCE-MIB::cmEthernetAccPortThresholdIndex.1.1.1.3.1.37 37
CM-PERFORMANCE-MIB::cmEthernetAccPortThresholdInterval.1.1.1.3.1.37 interval-1day
CM-PERFORMANCE-MIB::cmEthernetAccPortThresholdVariable.1.1.1.3.1.37 CM-PERFORMANCE-MIB::defaultThresholdTest.1.1.1.3.1
IF-MIB::ifName.2 Access PORT-1-1-1-3
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 0B 2D 0A 00 2D 06 00 "
ADVA-MIB::neEventLogIndex.79 79
ADVA-MIB::neEventLogTimeStamp.79 2018-12-10,11:45:10.8,-6:0
TRAP,
            'Access PORT-1-1-1-3 unknown threshold exceeded for interval-1day',
            'Could not handle cmEthernetAccPortThresholdCrossingAlert unknown threshold',
        );
    }
}
