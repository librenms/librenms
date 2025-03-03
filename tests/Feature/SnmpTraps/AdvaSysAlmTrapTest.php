<?php
/**
 * AdvaSysAlmTrapest.php
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

use LibreNMS\Enum\Severity;

class AdvaSysAlmTrapTest extends SnmpTrapTestCase
{
    public function testCriticalAlarm(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:0:15:22.68
SNMPv2-MIB::snmpTrapOID.0 CM-ALARM-MIB::cmSysAlmTrap
CM-ALARM-MIB::cmAlmIndex.5 5
CM-ALARM-MIB::cmSysAlmNotifCode.5 critical
CM-ALARM-MIB::cmSysAlmType.5 primntpsvrFailed
CM-ALARM-MIB::cmSysAlmSrvEff.5 nonServiceAffecting
CM-ALARM-MIB::cmSysAlmTime.5 2018-12-10,11:28:20.0,-6:0
CM-ALARM-MIB::cmSysAlmLocation.5 nearEnd
CM-ALARM-MIB::cmSysAlmDirection.5 receiveDirectionOnly
CM-ALARM-MIB::cmSysAlmDescr.5 "Critical alarm test"
TRAP,
            'System Alarm: Critical alarm test Status: critical',
            'Could not handle cmSysAlmTrap critical',
            [Severity::Error],
        );
    }

    public function testMajorAlarm(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:0:15:22.68
SNMPv2-MIB::snmpTrapOID.0 CM-ALARM-MIB::cmSysAlmTrap
CM-ALARM-MIB::cmAlmIndex.5 5
CM-ALARM-MIB::cmSysAlmNotifCode.5 major
CM-ALARM-MIB::cmSysAlmType.5 primntpsvrFailed
CM-ALARM-MIB::cmSysAlmSrvEff.5 nonServiceAffecting
CM-ALARM-MIB::cmSysAlmTime.5 2018-12-10,11:28:20.0,-6:0
CM-ALARM-MIB::cmSysAlmLocation.5 nearEnd
CM-ALARM-MIB::cmSysAlmDirection.5 receiveDirectionOnly
CM-ALARM-MIB::cmSysAlmDescr.5 "Major alarm test"
TRAP,
            'System Alarm: Major alarm test Status: major',
            'Could not handle cmSysAlmTrap major',
            [Severity::Warning],
        );
    }

    public function testMinorAlarm(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:0:15:22.68
SNMPv2-MIB::snmpTrapOID.0 CM-ALARM-MIB::cmSysAlmTrap
CM-ALARM-MIB::cmAlmIndex.5 5
CM-ALARM-MIB::cmSysAlmNotifCode.5 minor
CM-ALARM-MIB::cmSysAlmType.5 primntpsvrFailed
CM-ALARM-MIB::cmSysAlmSrvEff.5 nonServiceAffecting
CM-ALARM-MIB::cmSysAlmTime.5 2018-12-10,11:28:20.0,-6:0
CM-ALARM-MIB::cmSysAlmLocation.5 nearEnd
CM-ALARM-MIB::cmSysAlmDirection.5 receiveDirectionOnly
CM-ALARM-MIB::cmSysAlmDescr.5 "Minor alarm test"
TRAP,
            'System Alarm: Minor alarm test Status: minor',
            'Could not handle cmSysAlmTrap minor',
            [Severity::Notice],
        );
    }

    public function testClearedAlarm(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:0:15:22.68
SNMPv2-MIB::snmpTrapOID.0 CM-ALARM-MIB::cmSysAlmTrap
CM-ALARM-MIB::cmAlmIndex.5 5
CM-ALARM-MIB::cmSysAlmNotifCode.5 cleared
CM-ALARM-MIB::cmSysAlmType.5 primntpsvrFailed
CM-ALARM-MIB::cmSysAlmSrvEff.5 nonServiceAffecting
CM-ALARM-MIB::cmSysAlmTime.5 2018-12-10,11:28:20.0,-6:0
CM-ALARM-MIB::cmSysAlmLocation.5 nearEnd
CM-ALARM-MIB::cmSysAlmDirection.5 receiveDirectionOnly
CM-ALARM-MIB::cmSysAlmDescr.5 "Cleared alarm test"
TRAP,
            'System Alarm: Cleared alarm test Status: cleared',
            'Could not handle cmSysAlmTrap major',
            [Severity::Ok],
        );
    }
}
