<?php

/*
 * EesPowerAlarmTest.php
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * Tests for EES-POWER-MIB Emerson Energy System alarm traps.
 *
 * @package LibreNMS
 * @link https://www.librenms.org
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use LibreNMS\Enum\Severity;

final class EesPowerAlarmTest extends SnmpTrapTestCase
{
    public function testEesPowerAlarmActivated(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:37655->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 391:23:06:38.09
SNMPv2-MIB::snmpTrapOID.0 EES-POWER-MIB::alarmTrap
EES-POWER-MIB::alarmIndex 5697
EES-POWER-MIB::alarmTime 2026-5-13,18:22:24.0,+0:0
EES-POWER-MIB::alarmStatusChange activated
EES-POWER-MIB::alarmSeverity critical
EES-POWER-MIB::alarmDescription Under Voltage 1, its owner: Power System
EES-POWER-MIB::alarmType 77980
TRAP,
            'EES Power Alarm activated: Under Voltage 1, its owner: Power System | severity=critical | type=77980 | index=5697 | time=2026-5-13,18:22:24.0,+0:0',
            'Could not handle EES-POWER-MIB::alarmTrap activated trap',
            [Severity::Error],
        );
    }

    public function testEesPowerAlarmCleared(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:37655->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 391:23:12:34.28
SNMPv2-MIB::snmpTrapOID.0 EES-POWER-MIB::alarmTrap
EES-POWER-MIB::alarmIndex 5698
EES-POWER-MIB::alarmTime 2026-5-13,19:16:6.0,+0:0
EES-POWER-MIB::alarmStatusChange deactivated
EES-POWER-MIB::alarmSeverity critical
EES-POWER-MIB::alarmDescription Under Voltage 1, its owner: Power System
EES-POWER-MIB::alarmType 77980
TRAP,
            'EES Power Alarm cleared: Under Voltage 1, its owner: Power System | severity=critical | type=77980 | index=5698 | time=2026-5-13,19:16:6.0,+0:0',
            'Could not handle EES-POWER-MIB::alarmTrap cleared trap',
            [Severity::Ok],
        );
    }

    public function testEesPowerAlarmMajorMapsToError(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:37655->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 14:4:36:08.16
SNMPv2-MIB::snmpTrapOID.0 EES-POWER-MIB::alarmTrap
EES-POWER-MIB::alarmIndex 5711
EES-POWER-MIB::alarmTime 2026-5-13,19:21:0.0,+0:0
EES-POWER-MIB::alarmStatusChange activated
EES-POWER-MIB::alarmSeverity major
EES-POWER-MIB::alarmDescription Battery Current Limit Exceeded, its owner: Batt1
EES-POWER-MIB::alarmType 7614465
TRAP,
            'EES Power Alarm activated: Battery Current Limit Exceeded, its owner: Batt1 | severity=major | type=7614465 | index=5711 | time=2026-5-13,19:21:0.0,+0:0',
            'Could not handle EES-POWER-MIB::alarmTrap major alarm trap',
            [Severity::Error],
        );
    }

    public function testEesPowerAlarmActiveTrapIsSuppressed(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:37655->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 391:23:06:38.10
SNMPv2-MIB::snmpTrapOID.0 EES-POWER-MIB::alarmActiveTrap
EES-POWER-MIB::alarmTime 2026-5-13,18:22:24.0,+0:0
EES-POWER-MIB::alarmSeverity critical
EES-POWER-MIB::alarmDescription Under Voltage 1, its owner: Power System
EES-POWER-MIB::alarmType 77980
TRAP,
            [],
            'EES-POWER-MIB::alarmActiveTrap should be suppressed',
        );
    }

    public function testEesPowerAlarmCeaseTrapIsSuppressed(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:37655->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 391:23:12:34.29
SNMPv2-MIB::snmpTrapOID.0 EES-POWER-MIB::alarmCeaseTrap
EES-POWER-MIB::alarmTime 2026-5-13,19:16:6.0,+0:0
EES-POWER-MIB::alarmSeverity critical
EES-POWER-MIB::alarmDescription Under Voltage 1, its owner: Power System
EES-POWER-MIB::alarmType 77980
TRAP,
            [],
            'EES-POWER-MIB::alarmCeaseTrap should be suppressed',
        );
    }
}
