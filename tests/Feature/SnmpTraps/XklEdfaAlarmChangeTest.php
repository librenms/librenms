<?php

/**
 * XklEdfaAlarmChangeTest.php
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

final class XklEdfaAlarmChangeTest extends SnmpTrapTestCase
{
    public function testXklEdfaAlarmChangeDisabled(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:4:53:43.00
SNMPv2-MIB::snmpTrapOID.0 XKL-MIB::xklEDFAAlarmChange
XKL-MIB::xklEDFAIndex.1 1
XKL-MIB::xklEDFAInReset.1 no
XKL-MIB::xklEDFADisabled.1 enabled
XKL-MIB::xklEDFAMuted.1 no
XKL-MIB::xklEDFACaseTemperatureAlarm.1 no
XKL-MIB::xklEDFACommonAlarm.1 yes
XKL-MIB::xklEDFAPumpTemperatureAlarm.1 no
XKL-MIB::xklEDFALossOfInputAlarm.1 no
XKL-MIB::xklEDFALossOfOutputAlarm.1 no
XKL-MIB::xklEDFAModuleAlarms.1 NONE
XKL-MIB::xklEDFAName.1 Output EDFA
TRAP,

            'Output EDFA changed to disabled.',
            'Failed to handle XklEdfaAlarmChange EDFA disabled alarm trap.',
        );
    }

    public function testXklEdfaAlarmChangeReset(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:4:53:43.00
SNMPv2-MIB::snmpTrapOID.0 XKL-MIB::xklEDFAAlarmChange
XKL-MIB::xklEDFAIndex.1 1
XKL-MIB::xklEDFAInReset.1 yes
XKL-MIB::xklEDFADisabled.1 disabled
XKL-MIB::xklEDFAMuted.1 no
XKL-MIB::xklEDFACaseTemperatureAlarm.1 no
XKL-MIB::xklEDFACommonAlarm.1 yes
XKL-MIB::xklEDFAPumpTemperatureAlarm.1 no
XKL-MIB::xklEDFALossOfInputAlarm.1 no
XKL-MIB::xklEDFALossOfOutputAlarm.1 no
XKL-MIB::xklEDFAModuleAlarms.1 NONE
XKL-MIB::xklEDFAName.1 Output EDFA
TRAP,

            'Output EDFA reset.',
            'Failed to handle XklEdfaAlarmChange EDFA reset alarm trap.',
        );
    }

    public function testXklEdfaAlarmChangeMuted(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:4:53:43.00
SNMPv2-MIB::snmpTrapOID.0 XKL-MIB::xklEDFAAlarmChange
XKL-MIB::xklEDFAIndex.1 1
XKL-MIB::xklEDFAInReset.1 no
XKL-MIB::xklEDFADisabled.1 disabled
XKL-MIB::xklEDFAMuted.1 yes
XKL-MIB::xklEDFACaseTemperatureAlarm.1 no
XKL-MIB::xklEDFACommonAlarm.1 yes
XKL-MIB::xklEDFAPumpTemperatureAlarm.1 no
XKL-MIB::xklEDFALossOfInputAlarm.1 no
XKL-MIB::xklEDFALossOfOutputAlarm.1 no
XKL-MIB::xklEDFAModuleAlarms.1 NONE
XKL-MIB::xklEDFAName.1 Output EDFA
TRAP,

            'Output EDFA has become muted.',
            'Failed to handle XklEdfaAlarmChange EDFA muted alarm trap.',
        );
    }

    public function testXklEdfaAlarmChangeCaseTemp(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:4:53:43.00
SNMPv2-MIB::snmpTrapOID.0 XKL-MIB::xklEDFAAlarmChange
XKL-MIB::xklEDFAIndex.1 1
XKL-MIB::xklEDFAInReset.1 no
XKL-MIB::xklEDFADisabled.1 disabled
XKL-MIB::xklEDFAMuted.1 no
XKL-MIB::xklEDFACaseTemperatureAlarm.1 yes
XKL-MIB::xklEDFACommonAlarm.1 yes
XKL-MIB::xklEDFAPumpTemperatureAlarm.1 no
XKL-MIB::xklEDFALossOfInputAlarm.1 no
XKL-MIB::xklEDFALossOfOutputAlarm.1 no
XKL-MIB::xklEDFAModuleAlarms.1 NONE
XKL-MIB::xklEDFAName.1 Output EDFA
TRAP,

            'Output EDFA case temperature alarm is active.',
            'Failed to handle XklEdfaAlarmChange case temperature alarm trap.',
        );
    }

    public function testXklEdfaAlarmChangePumpTemp(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:4:53:43.00
SNMPv2-MIB::snmpTrapOID.0 XKL-MIB::xklEDFAAlarmChange
XKL-MIB::xklEDFAIndex.1 1
XKL-MIB::xklEDFAInReset.1 no
XKL-MIB::xklEDFADisabled.1 disabled
XKL-MIB::xklEDFAMuted.1 no
XKL-MIB::xklEDFACaseTemperatureAlarm.1 no
XKL-MIB::xklEDFACommonAlarm.1 yes
XKL-MIB::xklEDFAPumpTemperatureAlarm.1 yes
XKL-MIB::xklEDFALossOfInputAlarm.1 no
XKL-MIB::xklEDFALossOfOutputAlarm.1 no
XKL-MIB::xklEDFAModuleAlarms.1 NONE
XKL-MIB::xklEDFAName.1 Output EDFA
TRAP,

            'Output EDFA pump temperature alarm is active.',
            'Failed to handle XklEdfaAlarmChange pump temperature alarm trap.',
        );
    }

    public function testXklEdfaAlarmChangePumpBias(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:4:53:43.00
SNMPv2-MIB::snmpTrapOID.0 XKL-MIB::xklEDFAAlarmChange
XKL-MIB::xklEDFAIndex.1 1
XKL-MIB::xklEDFAInReset.1 no
XKL-MIB::xklEDFADisabled.1 disabled
XKL-MIB::xklEDFAMuted.1 no
XKL-MIB::xklEDFACaseTemperatureAlarm.1 no
XKL-MIB::xklEDFACommonAlarm.1 no
XKL-MIB::xklEDFAPumpTemperatureAlarm.1 no
XKL-MIB::xklEDFAPumpBiasAlarm yes
XKL-MIB::xklEDFALossOfInputAlarm.1 no
XKL-MIB::xklEDFALossOfOutputAlarm.1 no
XKL-MIB::xklEDFAModuleAlarms.1 NONE
XKL-MIB::xklEDFAName.1 Output EDFA
TRAP,

            'Output EDFA pump BIAS alarm is active.',
            'Failed to handle XklEdfaAlarmChange EDFA BIAS alarm trap.',
            [Severity::Error],
        );
    }

    public function testXklEdfaAlarmChangeLossInput(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:4:53:43.00
SNMPv2-MIB::snmpTrapOID.0 XKL-MIB::xklEDFAAlarmChange
XKL-MIB::xklEDFAIndex.1 1
XKL-MIB::xklEDFAInReset.1 no
XKL-MIB::xklEDFADisabled.1 disabled
XKL-MIB::xklEDFAMuted.1 no
XKL-MIB::xklEDFACaseTemperatureAlarm.1 no
XKL-MIB::xklEDFACommonAlarm.1 no
XKL-MIB::xklEDFAPumpTemperatureAlarm.1 no
XKL-MIB::xklEDFAPumpBiasAlarm no
XKL-MIB::xklEDFALossOfInputAlarm.1 yes
XKL-MIB::xklEDFALossOfOutputAlarm.1 no
XKL-MIB::xklEDFAModuleAlarms.1 NONE
XKL-MIB::xklEDFAName.1 Output EDFA
TRAP,

            'Output EDFA loss of input alarm is active.',
            'Failed to handle XklEdfaAlarmChange loss of input alarm trap.',
            [Severity::Error],
        );
    }

    public function testXklEdfaAlarmChangeLossOutput(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:4:53:43.00
SNMPv2-MIB::snmpTrapOID.0 XKL-MIB::xklEDFAAlarmChange
XKL-MIB::xklEDFAIndex.1 1
XKL-MIB::xklEDFAInReset.1 no
XKL-MIB::xklEDFADisabled.1 disabled
XKL-MIB::xklEDFAMuted.1 no
XKL-MIB::xklEDFACaseTemperatureAlarm.1 no
XKL-MIB::xklEDFACommonAlarm.1 no
XKL-MIB::xklEDFAPumpTemperatureAlarm.1 no
XKL-MIB::xklEDFAPumpBiasAlarm no
XKL-MIB::xklEDFALossOfInputAlarm.1 no
XKL-MIB::xklEDFALossOfOutputAlarm.1 yes
XKL-MIB::xklEDFAModuleAlarms.1 NONE
XKL-MIB::xklEDFAName.1 Output EDFA
TRAP,

            'Output EDFA loss of output alarm is active.',
            'Failed to handle XklEdfaAlarmChange loss of output alarm trap.',
            [Severity::Error],
        );
    }

    public function testXklEdfaAlarmChangeModule(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:4:53:43.00
SNMPv2-MIB::snmpTrapOID.0 XKL-MIB::xklEDFAAlarmChange
XKL-MIB::xklEDFAIndex.1 1
XKL-MIB::xklEDFAInReset.1 no
XKL-MIB::xklEDFADisabled.1 disabled
XKL-MIB::xklEDFAMuted.1 no
XKL-MIB::xklEDFACaseTemperatureAlarm.1 no
XKL-MIB::xklEDFACommonAlarm.1 no
XKL-MIB::xklEDFAPumpTemperatureAlarm.1 no
XKL-MIB::xklEDFAPumpBiasAlarm no
XKL-MIB::xklEDFALossOfInputAlarm.1 no
XKL-MIB::xklEDFALossOfOutputAlarm.1 yes
XKL-MIB::xklEDFAModuleAlarms.1 OSC 6
XKL-MIB::xklEDFAName.1 Output EDFA
TRAP,

            'Output EDFA $ module alarm: OSC 6',
            'Failed to handle XklEdfaAlarmChange loss of output alarm trap.',
            [Severity::Error],
        );
    }
}
