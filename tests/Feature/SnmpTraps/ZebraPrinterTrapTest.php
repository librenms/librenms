<?php

namespace LibreNMS\Tests\Feature\SnmpTraps;

use LibreNMS\Enum\Severity;

final class ZebraPrinterTrapTest extends SnmpTrapTestCase
{
    public function testZebraPrinterHeadOpen(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ERROR CONDITION: HEAD OPEN
TRAP,
            'SNMP Trap: Zebra Printer - ERROR CONDITION: HEAD OPEN',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 HEAD OPEN',
            [Severity::Warning, 'printer'],
        );
    }

    public function testZebraPrinterPaperOut(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: PAPER OUT
TRAP,
            'SNMP Trap: Zebra Printer - ALERT: PAPER OUT',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 PAPER OUT',
            [Severity::Error, 'printer'],
        );
    }

    public function testZebraPrinterRibbonOut(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: RIBBON OUT
TRAP,
            'SNMP Trap: Zebra Printer - ALERT: RIBBON OUT',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 RIBBON OUT',
            [Severity::Error, 'printer'],
        );
    }

    public function testZebraPrinterMediaLow(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: MEDIA LOW
TRAP,
            'SNMP Trap: Zebra Printer - ALERT: MEDIA LOW',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 MEDIA LOW',
            [Severity::Warning, 'printer'],
        );
    }

    public function testZebraPrinterJobCompleted(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: PQ JOB COMPLETED
TRAP,
            'SNMP Trap: Zebra Printer - ALERT: PQ JOB COMPLETED',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 PQ JOB COMPLETED',
            [Severity::Info, 'printer'],
        );
    }

    public function testZebraPrinterCutterJam(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: CUTTER JAM
TRAP,
            'SNMP Trap: Zebra Printer - ALERT: CUTTER JAM',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 CUTTER JAM',
            [Severity::Error, 'printer'],
        );
    }

    public function testZebraPrinterAlertCleared(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:37.14
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.2
ESI-MIB::psOutput.7 ERROR CLEARED: HEAD OPEN
TRAP,
            'SNMP Trap: Zebra Printer - ERROR CLEARED: HEAD OPEN',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.2 alert cleared',
            [Severity::Ok, 'printer'],
        );
    }
}
