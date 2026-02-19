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
            'ERROR CONDITION: HEAD OPEN',
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
            'ALERT: PAPER OUT',
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
            'ALERT: RIBBON OUT',
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
            'ALERT: MEDIA LOW',
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
            'ALERT: PQ JOB COMPLETED',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 PQ JOB COMPLETED',
            [Severity::Ok, 'printer'],
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
            'ALERT: CUTTER JAM',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 CUTTER JAM',
            [Severity::Error, 'printer'],
        );
    }

    public function testZebraPrinterJobCompletedGerman(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 MELDUNG: Druckauftr Fertg
TRAP,
            'MELDUNG: Druckauftr Fertg',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 PQ JOB COMPLETED (German)',
            [Severity::Ok, 'printer'],
        );
    }

    public function testZebraPrinterPaused(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: PRINTER PAUSED
TRAP,
            'ALERT: PRINTER PAUSED',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 PRINTER PAUSED',
            [Severity::Info, 'printer'],
        );
    }

    public function testZebraPrinterHeadElementBad(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: HEAD ELEMENT BAD
TRAP,
            'ALERT: HEAD ELEMENT BAD',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 HEAD ELEMENT BAD',
            [Severity::Error, 'printer'],
        );
    }

    public function testZebraPrinterReplaceHead(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: REPLACE HEAD
TRAP,
            'ALERT: REPLACE HEAD',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 REPLACE HEAD',
            [Severity::Error, 'printer'],
        );
    }

    public function testZebraPrinterMotorOvertemp(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: MOTOR OVERTEMP
TRAP,
            'ALERT: MOTOR OVERTEMP',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 MOTOR OVERTEMP',
            [Severity::Error, 'printer'],
        );
    }

    public function testZebraPrinterPrintheadShutdown(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: PRINTHEAD SHUTDOWN
TRAP,
            'ALERT: PRINTHEAD SHUTDOWN',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 PRINTHEAD SHUTDOWN',
            [Severity::Error, 'printer'],
        );
    }

    public function testZebraPrinterThermistorFault(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: THERMISTOR FAULT
TRAP,
            'ALERT: THERMISTOR FAULT',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 THERMISTOR FAULT',
            [Severity::Error, 'printer'],
        );
    }

    public function testZebraPrinterInvalidHead(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: INVALID HEAD
TRAP,
            'ALERT: INVALID HEAD',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 INVALID HEAD',
            [Severity::Error, 'printer'],
        );
    }

    public function testZebraPrinterMediaCartridgeLoadFailure(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: MEDIA CARTRIDGE LOAD FAILURE
TRAP,
            'ALERT: MEDIA CARTRIDGE LOAD FAILURE',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 MEDIA CARTRIDGE LOAD FAILURE',
            [Severity::Error, 'printer'],
        );
    }

    public function testZebraPrinterPaperError(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: PAPER ERROR
TRAP,
            'ALERT: PAPER ERROR',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 PAPER ERROR',
            [Severity::Error, 'printer'],
        );
    }

    public function testZebraPrinterRibbonAuthError(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: RIBBON AUTH ERROR
TRAP,
            'ALERT: RIBBON AUTH ERROR',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 RIBBON AUTH ERROR',
            [Severity::Error, 'printer'],
        );
    }

    public function testZebraPrinterHeadTooHot(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: HEAD TOO HOT
TRAP,
            'ALERT: HEAD TOO HOT',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 HEAD TOO HOT',
            [Severity::Warning, 'printer'],
        );
    }

    public function testZebraPrinterHeadCold(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: HEAD COLD
TRAP,
            'ALERT: HEAD COLD',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 HEAD COLD',
            [Severity::Warning, 'printer'],
        );
    }

    public function testZebraPrinterSupplyTooHot(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: SUPPLY TOO HOT
TRAP,
            'ALERT: SUPPLY TOO HOT',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 SUPPLY TOO HOT',
            [Severity::Warning, 'printer'],
        );
    }

    public function testZebraPrinterRibbonLow(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: RIBBON LOW
TRAP,
            'ALERT: RIBBON LOW',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 RIBBON LOW',
            [Severity::Warning, 'printer'],
        );
    }

    public function testZebraPrinterBatteryLow(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: BATTERY LOW
TRAP,
            'ALERT: BATTERY LOW',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 BATTERY LOW',
            [Severity::Warning, 'printer'],
        );
    }

    public function testZebraPrinterCleanPrinthead(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: CLEAN PRINTHEAD
TRAP,
            'ALERT: CLEAN PRINTHEAD',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 CLEAN PRINTHEAD',
            [Severity::Warning, 'printer'],
        );
    }

    public function testZebraPrinterRfidError(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: RFID ERROR
TRAP,
            'ALERT: RFID ERROR',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 RFID ERROR',
            [Severity::Warning, 'printer'],
        );
    }

    public function testZebraPrinterRewind(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: REWIND
TRAP,
            'ALERT: REWIND',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 REWIND',
            [Severity::Warning, 'printer'],
        );
    }

    public function testZebraPrinterNoReaderPresent(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: NO READER PRESENT
TRAP,
            'ALERT: NO READER PRESENT',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 NO READER PRESENT',
            [Severity::Warning, 'printer'],
        );
    }

    public function testZebraPrinterBatteryMissing(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: BATTERY MISSING
TRAP,
            'ALERT: BATTERY MISSING',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 BATTERY MISSING',
            [Severity::Warning, 'printer'],
        );
    }

    public function testZebraPrinterMediaCartridgeEjectFailure(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: MEDIA CARTRIDGE EJECT FAILURE
TRAP,
            'ALERT: MEDIA CARTRIDGE EJECT FAILURE',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 MEDIA CARTRIDGE EJECT FAILURE',
            [Severity::Warning, 'printer'],
        );
    }

    public function testZebraPrinterMediaCartridgeForcedEject(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: MEDIA CARTRIDGE FORCED EJECT
TRAP,
            'ALERT: MEDIA CARTRIDGE FORCED EJECT',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 MEDIA CARTRIDGE FORCED EJECT',
            [Severity::Warning, 'printer'],
        );
    }

    public function testZebraPrinterRibbonTension(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: RIBBON TENSION
TRAP,
            'ALERT: RIBBON TENSION',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 RIBBON TENSION',
            [Severity::Warning, 'printer'],
        );
    }

    public function testZebraPrinterCoverOpen(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: COVER OPEN
TRAP,
            'ALERT: COVER OPEN',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 COVER OPEN',
            [Severity::Warning, 'printer'],
        );
    }

    public function testZebraPrinterCleanCutter(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: CLEAN CUTTER
TRAP,
            'ALERT: CLEAN CUTTER',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 CLEAN CUTTER',
            [Severity::Warning, 'printer'],
        );
    }

    public function testZebraPrinterDuplicateIp(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: DUPLICATE IP
TRAP,
            'ALERT: DUPLICATE IP',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 DUPLICATE IP',
            [Severity::Warning, 'printer'],
        );
    }

    public function testZebraPrinterBasicForced(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: BASIC FORCED
TRAP,
            'ALERT: BASIC FORCED',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 BASIC FORCED',
            [Severity::Warning, 'printer'],
        );
    }

    public function testZebraPrinterCountryCodeError(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: COUNTRY CODE ERROR
TRAP,
            'ALERT: COUNTRY CODE ERROR',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 COUNTRY CODE ERROR',
            [Severity::Warning, 'printer'],
        );
    }

    public function testZebraPrinterBasicRuntime(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: BASIC RUNTIME
TRAP,
            'ALERT: BASIC RUNTIME',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 BASIC RUNTIME',
            [Severity::Info, 'printer'],
        );
    }

    public function testZebraPrinterSgdSet(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: SGD SET
TRAP,
            'ALERT: SGD SET',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 SGD SET',
            [Severity::Info, 'printer'],
        );
    }

    public function testZebraPrinterShuttingDown(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: SHUTTING DOWN
TRAP,
            'ALERT: SHUTTING DOWN',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 SHUTTING DOWN',
            [Severity::Info, 'printer'],
        );
    }

    public function testZebraPrinterRestarting(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: RESTARTING
TRAP,
            'ALERT: RESTARTING',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 RESTARTING',
            [Severity::Info, 'printer'],
        );
    }

    public function testZebraPrinterPmcuDownload(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: PMCU DOWNLOAD
TRAP,
            'ALERT: PMCU DOWNLOAD',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 PMCU DOWNLOAD',
            [Severity::Info, 'printer'],
        );
    }

    public function testZebraPrinterCountryCode(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: COUNTRY CODE
TRAP,
            'ALERT: COUNTRY CODE',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 COUNTRY CODE',
            [Severity::Info, 'printer'],
        );
    }

    public function testZebraPrinterMediaCartridge(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: MEDIA CARTRIDGE
TRAP,
            'ALERT: MEDIA CARTRIDGE',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 MEDIA CARTRIDGE',
            [Severity::Info, 'printer'],
        );
    }

    public function testZebraPrinterCleaningMode(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: CLEANING MODE
TRAP,
            'ALERT: CLEANING MODE',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 CLEANING MODE',
            [Severity::Info, 'printer'],
        );
    }

    public function testZebraPrinterLabelReady(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: LABEL READY
TRAP,
            'ALERT: LABEL READY',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 LABEL READY',
            [Severity::Ok, 'printer'],
        );
    }

    public function testZebraPrinterRibbonIn(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: RIBBON IN
TRAP,
            'ALERT: RIBBON IN',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 RIBBON IN',
            [Severity::Ok, 'printer'],
        );
    }

    public function testZebraPrinterPowerOn(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: POWER ON
TRAP,
            'ALERT: POWER ON',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 POWER ON',
            [Severity::Ok, 'printer'],
        );
    }

    public function testZebraPrinterColdStart(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:44298->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 19:3:47:23.13
SNMPv2-MIB::snmpTrapOID.0 ZEBRA-QL-MIB::zebra.1.0.1
ESI-MIB::psOutput.7 ALERT: COLD START
TRAP,
            'ALERT: COLD START',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.1 COLD START',
            [Severity::Ok, 'printer'],
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
            'ERROR CLEARED: HEAD OPEN',
            'Failed to handle ZEBRA-QL-MIB::zebra.1.0.2 alert cleared',
            [Severity::Ok, 'printer'],
        );
    }
}
