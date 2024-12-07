<?php
/*
 * CyberPowerTrapsTest.php
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
 * Unit tests for CyberPower UPS SNMP trap handlers
 *
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2020 KanREN, Inc
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

namespace LibreNMS\Tests\Feature\SnmpTraps;

use LibreNMS\Enum\Severity;

class CyberPowerTrapsTest extends SnmpTrapTestCase
{
    public function testCpUpsOverload(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsOverload
CPS-MIB::mtrapinfoString "The UPS has sensed an overload condition."
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 "comstring"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps
TRAP,
            'The UPS has sensed an overload condition.',
            'Could not handle CpUpsOverload trap',
            [Severity::Error],
        );
    }

    public function testCpUpsDiagFailed(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsDiagnosticsFailed
CPS-MIB::mtrapinfoString "The UPS battery test failed."
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 "comstring"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps
TRAP,
            'The UPS battery test failed.',
            'Could not handle CpUpsDiagFailed trap',
            [Severity::Error],
        );
    }

    public function testCpUpsDischarged(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsDischarged
CPS-MIB::mtrapinfoString "The UPS has started a runtime calibration process."
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 "comstring"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps
TRAP,
            'The UPS has started a runtime calibration process.',
            'Could not handle CpUpsDischarged trap',
        );
    }

    public function testCpUpsOnBattery(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsOnBattery
CPS-MIB::mtrapinfoString "Utility power failed, transfer to backup mode."
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 "comstring"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps
TRAP,
            'Utility power failed, transfer to backup mode.',
            'Could not handle CpUpsOnBattery trap',
            [Severity::Warning],
        );
    }

    public function testCpLowBattery(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::lowBattery
CPS-MIB::mtrapinfoString "The UPS battery capacity is low than threshold, soon to be exhausted."
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 "comstring"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps
TRAP,
            'The UPS battery capacity is low than threshold, soon to be exhausted.',
            'Could not handle CpLowBattery trap',
            [Severity::Warning],
        );
    }

    public function testCpPowerRestored(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::powerRestored
CPS-MIB::mtrapinfoString "Utility power restored, return from backup mode."
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 "comstring"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps
TRAP,
            'Utility power restored, return from backup mode.',
            'Could not handle CpPowerRestored trap',
            [Severity::Ok],
        );
    }

    public function testCpUpsDiagPassed(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsDiagnosticsPassed
CPS-MIB::mtrapinfoString "The UPS battery test passed."
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 "comstring"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps
TRAP,
            'The UPS battery test passed.',
            'Could not handle CpUpsDiagPassed trap',
        );
    }

    public function testCpRtnLowBattery(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::returnFromLowBattery
CPS-MIB::mtrapinfoString "The UPS has returned from a low battery condition."
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 "comstring"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps
TRAP,
            'The UPS has returned from a low battery condition.',
            'Could not handle CpRtnLowBattery trap',
            [Severity::Ok],
        );
    }

    public function testCpUpsTurnedOff(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsTurnedOff
CPS-MIB::mtrapinfoString "The UPS has been turned off."
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 "comstring"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps
TRAP,
            'The UPS has been turned off.',
            'Could not handle CpUpsTurnedOff trap',
            [Severity::Warning],
        );
    }

    public function testCpUpsSleeping(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsSleeping
CPS-MIB::mtrapinfoString "The UPS entered sleep mode. Output power will not be provided."
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 "comstring"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps
TRAP,
            'The UPS entered sleep mode. Output power will not be provided.',
            'Could not handle CpUpsSleeping trap',
            [Severity::Warning],
        );
    }

    public function testCpUpsWokeUp(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsWokeUp
CPS-MIB::mtrapinfoString "The UPS woke up from sleep mode. Output power is being provided."
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 "comstring"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps
TRAP,
            'The UPS woke up from sleep mode. Output power is being provided.',
            'Could not handle CpUpsWokeUp trap',
            [Severity::Ok],
        );
    }

    public function testCpUpsRebootStarted(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsRebootStarted
CPS-MIB::mtrapinfoString "The UPS started reboot sequence."
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 "comstring"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps
TRAP,
            'The UPS started reboot sequence.',
            'Could not handle CpUpsRebootStarted trap',
            [Severity::Warning],
        );
    }

    public function testCpUpsOverTemp(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsOverTemp
CPS-MIB::mtrapinfoString "The UPS inner temperature is too high."
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 "comstring"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps
TRAP,
            'The UPS inner temperature is too high.',
            'Could not handle CpUpsOverTemp trap',
            [Severity::Error],
        );
    }

    public function testCpRtnOverTemp(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::returnFromOverTemp
CPS-MIB::mtrapinfoString "The UPS over temperature condition cleared."
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 "comstring"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps
TRAP,
            'The UPS over temperature condition cleared.',
            'Could not handle CpRtnOverTemp trap',
            [Severity::Ok],
        );
    }

    public function testCpRtOverLoad(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::returnFromOverLoad
CPS-MIB::mtrapinfoString "The UPS has returned from an overload condition."
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 "comstring"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps
TRAP,
            'The UPS has returned from an overload condition.',
            'Could not handle CpRtOverLoad trap',
            [Severity::Ok],
        );
    }

    public function testCpRtnDischarged(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::returnFromDischarged
CPS-MIB::mtrapinfoString "The UPS runtime calibration completed."
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 "comstring"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps
TRAP,
            'The UPS runtime calibration completed.',
            'Could not handle CpRtnDischarged trap',
            [Severity::Ok],
        );
    }

    public function testCpUpsChargerFailure(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsChargerFailure
CPS-MIB::mtrapinfoString "The battery charger is abnormal."
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 "comstring"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps
TRAP,
            'The battery charger is abnormal.',
            'Could not handle CpUpsChargerFailure trap',
            [Severity::Warning],
        );
    }

    public function testCpRtnChargerFailure(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::returnFromChargerFailure
CPS-MIB::mtrapinfoString "The charger returned from a failure condition."
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 "comstring"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps
TRAP,
            'The charger returned from a failure condition.',
            'Could not handle CpRtnChargerFailure trap',
            [Severity::Ok],
        );
    }

    public function testCpUpsBatteryNotPresent(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:161->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 488:17:19:10.00
SNMPv2-MIB::snmpTrapOID.0 CPS-MIB::upsBatteryNotPresent
CPS-MIB::mtrapinfoString "Battery is not present."
SNMP-COMMUNITY-MIB::snmpTrapAddress.0 $device->ip
SNMP-COMMUNITY-MIB::snmpTrapCommunity.0 "comstring"
SNMPv2-MIB::snmpTrapEnterprise.0 CPS-MIB::cps
TRAP,
            'Battery is not present.',
            'Could not handle CpUpsBatteryNotPresent trap',
        );
    }
}
