<?php

namespace LibreNMS\Tests\Feature\SnmpTraps;

use LibreNMS\Enum\Severity;

final class VsolDataAlarmTrapTest extends SnmpTrapTestCase
{
    public function testOnuDeregister(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:01:23:45.67
SNMPv2-MIB::snmpTrapOID.0 V1600GSwitch::dataAlarmTrap
V1600GSwitch::dataUpLinkPort.0 ""
V1600GSwitch::dataPon.0 1
V1600GSwitch::dataOnu.0 3
V1600GSwitch::dataOnuPort.0 ""
V1600GSwitch::dataTrapOID.0 ""
V1600GSwitch::dataTrapClass.0 ""
V1600GSwitch::dataMac.0 ""
V1600GSwitch::dataTime.0 ""
V1600GSwitch::dateAlarmLevel.0 criterr
V1600GSwitch::dataValue.0 ""
V1600GSwitch::dataAlarmType.0 19
V1600GSwitch::dataSynOID.0 ""
TRAP,
            'onu-deregister (PON 1 ONU 3)',
            'Could not handle VsolDataAlarmTrap onu-deregister',
            [Severity::Error],
        );
    }

    public function testOnuRegister(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:01:23:45.67
SNMPv2-MIB::snmpTrapOID.0 V1600GSwitch::dataAlarmTrap
V1600GSwitch::dataUpLinkPort.0 ""
V1600GSwitch::dataPon.0 1
V1600GSwitch::dataOnu.0 3
V1600GSwitch::dataOnuPort.0 ""
V1600GSwitch::dataTrapOID.0 ""
V1600GSwitch::dataTrapClass.0 ""
V1600GSwitch::dataMac.0 ""
V1600GSwitch::dataTime.0 ""
V1600GSwitch::dateAlarmLevel.0 info
V1600GSwitch::dataValue.0 ""
V1600GSwitch::dataAlarmType.0 41
V1600GSwitch::dataSynOID.0 ""
TRAP,
            'onu-register (PON 1 ONU 3)',
            'Could not handle VsolDataAlarmTrap onu-register',
            [Severity::Ok],
        );
    }

    public function testPonLos(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:01:23:45.67
SNMPv2-MIB::snmpTrapOID.0 V1600GSwitch::dataAlarmTrap
V1600GSwitch::dataUpLinkPort.0 ""
V1600GSwitch::dataPon.0 1
V1600GSwitch::dataOnu.0 ""
V1600GSwitch::dataOnuPort.0 ""
V1600GSwitch::dataTrapOID.0 ""
V1600GSwitch::dataTrapClass.0 ""
V1600GSwitch::dataMac.0 ""
V1600GSwitch::dataTime.0 ""
V1600GSwitch::dateAlarmLevel.0 major
V1600GSwitch::dataValue.0 ""
V1600GSwitch::dataAlarmType.0 18
V1600GSwitch::dataSynOID.0 ""
TRAP,
            'pon-los (PON 1)',
            'Could not handle VsolDataAlarmTrap pon-los',
            [Severity::Error],
        );
    }

    public function testOnuRxPowerLowWithValue(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:01:23:45.67
SNMPv2-MIB::snmpTrapOID.0 V1600GSwitch::dataAlarmTrap
V1600GSwitch::dataUpLinkPort.0 ""
V1600GSwitch::dataPon.0 1
V1600GSwitch::dataOnu.0 2
V1600GSwitch::dataOnuPort.0 ""
V1600GSwitch::dataTrapOID.0 ""
V1600GSwitch::dataTrapClass.0 ""
V1600GSwitch::dataMac.0 ""
V1600GSwitch::dataTime.0 ""
V1600GSwitch::dateAlarmLevel.0 warning
V1600GSwitch::dataValue.0 -29.5
V1600GSwitch::dataAlarmType.0 48
V1600GSwitch::dataSynOID.0 ""
TRAP,
            'onu-pon-rxpower-low (PON 1 ONU 2) value=-29.5',
            'Could not handle VsolDataAlarmTrap onu-pon-rxpower-low',
            [Severity::Warning],
        );
    }
}
