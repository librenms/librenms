<?php
/**
 * AdvaAttributeChangeTest.php
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

class AdvaAttributeChangeTest extends SnmpTrapTestCase
{
    public function testSyslogIPVersionModified(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-SYSTEM-MIB::sysLogIpVersion.1 ipv6
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 09 0B 28 00 2D 06 00 "
ADVA-MIB::neEventLogIndex.150 150
ADVA-MIB::neEventLogTimeStamp.150 2018-12-10,9:11:40.5,-6:0
TRAP,
            'Syslog server 1 IP version set to ipv6',
            'Could not handle cmAttributeValueChangeTrap IP version modified',
        );
    }

    public function testSyslogIP6AddrModified(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-SYSTEM-MIB::sysLogIpv6Addr.1 2001:49d0:3c0c:0:0:0:0:1
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 09 0B 28 00 2D 06 00 "
ADVA-MIB::neEventLogIndex.150 150
ADVA-MIB::neEventLogTimeStamp.150 2018-12-10,9:11:40.5,-6:0
TRAP,
            'Syslog server 1 IP address changed to 2001:49d0:3c0c:0:0:0:0:1',
            'Could not handle cmAttributeValueChangeTrap IPv6 address modified'
        );
    }

    public function testSyslogIPAddrModified(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-SYSTEM-MIB::sysLogIpAddress.1 192.168.1.1
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 09 0B 28 00 2D 06 00 "
ADVA-MIB::neEventLogIndex.150 150
ADVA-MIB::neEventLogTimeStamp.150 2018-12-10,9:11:40.5,-6:0
TRAP,
            'Syslog server 1 IP address changed to 192.168.1.1',
            'Could not handle cmAttributeValueChangeTrap IPv4 address modified'
        );
    }

    public function testSyslogPortModified(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-SYSTEM-MIB::sysLogPort.1 514
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 09 0B 28 00 2D 06 00 "
ADVA-MIB::neEventLogIndex.150 150
ADVA-MIB::neEventLogTimeStamp.150 2018-12-10,9:11:40.5,-6:0
TRAP,
            'Syslog server 1 port changed to 514',
            'Could not handle cmAttributeValueChangeTrap port modified'
        );
    }

    public function testAclModified(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-SYSTEM-MIB::aclEntryEnabled.5 false
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 09 11 16 00 2D 06 00 "
ADVA-MIB::neEventLogIndex.155 155
ADVA-MIB::neEventLogTimeStamp.155 2018-12-10,9:17:22.5,-6:0
TRAP,
            'ACL 5 modified',
            'Could not handle cmAttributeValueChangeTrap ACL entry modified'
        );
    }

    public function testBannerModified(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-SYSTEM-MIB::securityBanner.0 Test MoTD
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 09 12 2B 00 2D 06 00 "
ADVA-MIB::neEventLogIndex.157 157
ADVA-MIB::neEventLogTimeStamp.157 2018-12-10,9:18:43.6,-6:0
TRAP,
            'MOTD/Banner modified',
            'Could not handle cmAttributeValueChangeTrap banner modified'
        );
    }

    public function testTimeSourceModified(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-SYSTEM-MIB::sysTimeOfDayType.0 ntp
F3-PTP-MIB::f3PtpSysTimeOfDayClock.0 SNMPv2-SMI::zeroDotZero
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 09 1C 39 00 2D 06 00 "
ADVA-MIB::neEventLogIndex.169 169
ADVA-MIB::neEventLogTimeStamp.169 2018-12-10,9:28:57.1,-6:0
TRAP,
            'Time source set to ntp',
            'Could not handle cmAttributeValueChangeTrap time source modified'
        );

        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-SYSTEM-MIB::sysTimeOfDayType.0 local
F3-PTP-MIB::f3PtpSysTimeOfDayClock.0 SNMPv2-SMI::zeroDotZero
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 09 1C 39 00 2D 06 00 "
ADVA-MIB::neEventLogIndex.169 169
ADVA-MIB::neEventLogTimeStamp.169 2018-12-10,9:28:57.1,-6:0
TRAP,
            'Time source set to local',
            'Could not handle cmAttributeValueChangeTrap time source modified'
        );
    }

    public function testTimeZoneModified(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
F3-TIMEZONE-MIB::f3TimeZoneDstControlEnabled.0 true
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 09 14 21 00 2D 06 00 "
ADVA-MIB::neEventLogIndex.158 158
ADVA-MIB::neEventLogTimeStamp.158 2018-12-10,9:20:33.5,-6:0
TRAP,
            'Daylight Savings Time enabled',
            'Could not handle cmAttributeValueChangeTrap DST enabled'
        );

        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
F3-TIMEZONE-MIB::f3TimeZoneDstControlEnabled.0 false
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 09 14 21 00 2D 06 00 "
ADVA-MIB::neEventLogIndex.158 158
ADVA-MIB::neEventLogTimeStamp.158 2018-12-10,9:20:33.5,-6:0
TRAP,
            'Daylight Savings Time disabled',
            'Could not handle cmAttributeValueChangeTrap DST disabled'
        );

        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
F3-TIMEZONE-MIB::f3TimeZoneUtcOffset.0 -05:00
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 0A 15 1E 00 2D 05 00 "
ADVA-MIB::neEventLogIndex.160 160
ADVA-MIB::neEventLogTimeStamp.160 2018-12-10,10:21:30.3,-5:0
TRAP,
            'UTC offset (timezone) change to -05:00',
            'Could not handle cmAttributeValueChangeTrap UTC offset modified'
        );
    }

    public function testNtpModified(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-SYSTEM-MIB::ntpPrimaryServer.0 192.168.2.2
CM-SYSTEM-MIB::ntpPrimaryServerIpVersion.0 ipv4
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 09 1E 11 00 2D 06 00 "
ADVA-MIB::neEventLogIndex.170 170
ADVA-MIB::neEventLogTimeStamp.170 2018-12-10,9:30:17.0,-6:0
TRAP,
            'Primary NTP server IP changed to 192.168.2.2',
            'Could not handle cmAttributeValueChangeTrap NTP primary server modified'
        );

        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-SYSTEM-MIB::ntpBackupServer.0 192.168.2.1
CM-SYSTEM-MIB::ntpBackupServerIpVersion.0 ipv4
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 09 1E 11 00 2D 06 00 "
ADVA-MIB::neEventLogIndex.170 170
ADVA-MIB::neEventLogTimeStamp.170 2018-12-10,9:30:17.0,-6:0
TRAP,
            'Backup NTP server IP changed to 192.168.2.1',
            'Could not handle cmAttributeValueChangeTrap NTP backup server modified'
        );
    }

    public function testAuthServerModified(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-SECURITY-MIB::cmRemoteAuthServerAccountingPort.3 1810
CM-SECURITY-MIB::cmRemoteAuthServerPort.3 1811
CM-SECURITY-MIB::cmRemoteAuthServerIpAddress.3 192.168.1.1
CM-SECURITY-MIB::cmRemoteAuthServerSecret.3 *****
CM-SECURITY-MIB::cmRemoteAuthServerEnabled.3 true
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 09 20 12 00 2D 06 00 "
ADVA-MIB::neEventLogIndex.173 173
ADVA-MIB::neEventLogTimeStamp.173 2018-12-10,9:32:18.1,-6:0
TRAP,
            [
                'Authentication server 3 IP changed to 192.168.1.1',
                'Authentication server 3 secret changed',
                'Authentication server 3 enabled',
            ],
            'Could not handle cmAttributeValueChangeTrap authentication server modified'
        );

        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-SECURITY-MIB::cmRemoteAuthServerEnabled.3 false
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 09 20 12 00 2D 06 00 "
ADVA-MIB::neEventLogIndex.173 173
ADVA-MIB::neEventLogTimeStamp.173 2018-12-10,9:32:18.1,-6:0
TRAP,
            'Authentication server 3 disabled',
            'Could not handle cmAttributeValueChangeTrap authentication server disabled');
    }

    public function testNeModified(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-ENTITY-MIB::neName.1 adva-test-1
CM-ENTITY-MIB::neCmdPromptPrefix.1 adva-test-1-prompt
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 09 22 17 00 2D 06 00 "
ADVA-MIB::neEventLogIndex.175 175
ADVA-MIB::neEventLogTimeStamp.175 2018-12-10,9:34:23.0,-6:0
TRAP,
            [
                'Network Element name changed to adva-test-1',
                'Network Element prompt changed to adva-test-1-prompt',
            ],
            'Could not handle cmAttributeValueChangeTrap network element modified'
        );
    }

    public function testSnmpDyingGaspStateModified(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-ENTITY-MIB::ethernetNTEGE114ProCardSnmpDyingGaspEnabled.1.1.1 true
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 09 24 0E 00 2D 06 00 "
ADVA-MIB::neEventLogIndex.177 177
ADVA-MIB::neEventLogTimeStamp.177 2018-12-10,9:36:14.5,-6:0
TRAP,
            'SNMP Dying Gasp is enabled',
            'Could not handle cmAttributeValueChangeTrap SNMP dying gasp enabled');

        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-ENTITY-MIB::ethernetNTEGE114ProCardSnmpDyingGaspEnabled.1.1.1 false
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 09 24 0E 00 2D 06 00 "
ADVA-MIB::neEventLogIndex.177 177
ADVA-MIB::neEventLogTimeStamp.177 2018-12-10,9:36:14.5,-6:0
TRAP,
            'SNMP Dying Gasp is disabled',
            'Could not handle cmAttributeValueChangeTrap SNMP dying gasp disabled');
    }

    public function testNetPortModified(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-FACILITY-MIB::cmEthernetNetPortConfigSpeed.1.1.1.2 speed-auto-100MB-full
CM-FACILITY-MIB::cmEthernetNetPortMediaType.1.1.1.2 copper
CM-FACILITY-MIB::cmEthernetNetPortMDIXType.1.1.1.2 crossed
CM-FACILITY-MIB::cmEthernetNetPortAutoDiagEnabled.1.1.1.2 false
CM-FACILITY-MIB::cmEthernetNetPortAdminState.1.1.1.2 in-service
CM-FACILITY-MIB::cmEthernetNetPortMTU.1.1.1.2 9000
CM-FACILITY-MIB::cmEthernetNetPortConfigSpeed.1.1.1.2 speed-auto-100MB-full
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 09 29 31 00 2D 06 00 "
ADVA-MIB::neEventLogIndex.188 188
ADVA-MIB::neEventLogTimeStamp.188 2018-12-10,9:41:49.0,-6:0
TRAP,
            [
                'Network Port 1-1-1-2 changed speed to speed-auto-100MB-full',
                'Network Port 1-1-1-2 changed media to copper',
                'Network Port 1-1-1-2 changed MDIX to crossed',
                'Network Port 1-1-1-2 AutoDiagnostic disabled',
                'Network Port 1-1-1-2 administrative state changed to in-service',
                'Network Port 1-1-1-2 MTU changed to 9000 bytes',
            ],
            'Could not handle cmAttributeValueChangeTrap network port modified specific messages');

        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-FACILITY-MIB::cmEthernetNetPortFakeOID.1.1.1.2 TestGenericMessage
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 09 29 31 00 2D 06 00 "
ADVA-MIB::neEventLogIndex.188 188
ADVA-MIB::neEventLogTimeStamp.188 2018-12-10,9:41:49.0,-6:0
TRAP,
            'Network Port 1-1-1-2 modified',
            'Could not handle cmAttributeValueChangeTrap network port modified generic message');
    }

    public function testAccPortModied(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-FACILITY-MIB::cmEthernetAccPortMediaType.1.1.1.4 fiber
CM-FACILITY-MIB::cmEthernetAccPortAutoDiagEnabled.1.1.1.4 false
CM-FACILITY-MIB::cmEthernetAccPortConfigSpeed.1.1.1.4 speed-auto-1000MB-full
CM-FACILITY-MIB::cmEthernetAccPortMDIXType.1.1.1.4 not-applicable
CM-FACILITY-MIB::cmEthernetAccPortMTU.1.1.1.4 9000
CM-FACILITY-MIB::cmEthernetAccPortAdminState.1.1.1.4 maintenance
CM-FACILITY-MIB::cmAccPortExtRefPrioMapProfile.1.1.1.4 SNMPv2-SMI::zeroDotZero
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 09 2B 16 00 2D 06 00 "
ADVA-MIB::neEventLogIndex.214 214
ADVA-MIB::neEventLogTimeStamp.214 2018-12-10,9:43:22.3,-6:0
TRAP,
            [
                'Access Port 1-1-1-4 changed speed to speed-auto-1000MB-full',
                'Access Port 1-1-1-4 changed media to fiber',
                'Access Port 1-1-1-4 changed MDIX to not-applicable',
                'Access Port 1-1-1-4 AutoDiagnostic disabled',
                'Access Port 1-1-1-4 administrative state changed to maintenance',
                'Access Port 1-1-1-4 MTU changed to 9000 bytes',
            ],
            'Could not handle cmAttributeValueChangeTrap access port modified specific messages');

        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-FACILITY-MIB::cmEthernetAccPortA2NPushPVIDEnabled.1.1.1.4 false
CM-FACILITY-MIB::cmAccPortExtRefPrioMapProfile.1.1.1.4 SNMPv2-SMI::zeroDotZero
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 09 2B 16 00 2D 06 00 "
ADVA-MIB::neEventLogIndex.214 214
ADVA-MIB::neEventLogTimeStamp.214 2018-12-10,9:43:22.3,-6:0
TRAP,
            'Access Port 1-1-1-4 modified',
            'Could not handle cmAttributeValueChangeTrap access port modified gerneric messages');
    }

    public function testAccFlowModified(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-FACILITY-MIB::cmFlowN2AEIR.1.1.1.4.1 0
CM-FACILITY-MIB::cmFlowN2ACIR.1.1.1.4.1 0
CM-FACILITY-MIB::cmFlowN2ACIRHi.1.1.1.4.1 0
CM-FACILITY-MIB::cmFlowN2AEIRHi.1.1.1.4.1 0
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 09 07 1C 00 2D 06 00 "
ADVA-MIB::neEventLogIndex.147 147
ADVA-MIB::neEventLogTimeStamp.147 2018-12-10,9:7:28.1,-6:0
TRAP,
            'Access Flow 1-1-1-4-1 modified',
            'Could not handle cmAttributeValueChangeTrap access flow modified');
    }

    public function testLagModified(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
F3-LAG-MIB::f3LagName.1.1 LagTest
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 08 3A 2B 00 2D 06 00 "
ADVA-MIB::neEventLogIndex.113 113
ADVA-MIB::neEventLogTimeStamp.113 2018-12-10,8:58:43.7,-6:0
TRAP,
            'LAG 1 modified',
            'Could not handle cmAttributeValueChangeTrap LAG modified');
    }

    public function testQosFlowPolicerModfied(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-FACILITY-MIB::cmQosFlowPolicerCIRLo.1.1.1.3.1.1.1 9856000
CM-FACILITY-MIB::cmQosFlowPolicerCIRHi.1.1.1.3.1.1.1 0
CM-FACILITY-MIB::cmQosFlowPolicerEntry.21.1.1.1.3.1.1.1 9856000
CM-FACILITY-MIB::cmQosFlowPolicerEntry.20.1.1.1.3.1.1.1 0
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 09 2F 33 00 2D 06 00 "
ADVA-MIB::neEventLogIndex.217 217
ADVA-MIB::neEventLogTimeStamp.217 2018-12-10,9:47:51.0,-6:0
TRAP,
            'QoS on flow 1-1-1-3-1 modified',
            'Could not handle cmAttributeValueChangeTrap QoS flow policer');
    }

    public function testQosShaperModified(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-FACILITY-MIB::cmQosShaperCIR.1.1.1.3.1.1.1 9856000
CM-FACILITY-MIB::cmQosShaperCIRHi.1.1.1.3.1.1.1 0
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 09 2F 33 00 2D 06 00 "
ADVA-MIB::neEventLogIndex.218 218
ADVA-MIB::neEventLogTimeStamp.218 2018-12-10,9:47:51.0,-6:0
TRAP,
            'QoS on flow 1-1-1-3-1 modified',
            'Could not handle cmAttributeValueChangeTrap QoS shaper');
    }

    public function testAccShaper(): void
    {
        $this->assertTrapLogsMessage(<<<'TRAP'
{{ hostname }}
UDP: [{{ ip }}]:57602->[192.168.5.5]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-FACILITY-MIB::cmAccPortQosShaperCIRLo.1.1.1.4.1 128000
CM-FACILITY-MIB::cmAccPortQosShaperCIRHi.1.1.1.4.1 0
RMON2-MIB::probeDateTime.0 "07 E2 0C 0A 09 07 1C 00 2D 06 00 "
ADVA-MIB::neEventLogIndex.146 146
ADVA-MIB::neEventLogTimeStamp.146 2018-12-10,9:7:28.1,-6:0
TRAP,
            'Shaper modified on access port 1-1-1-4-1 modified',
            'Could not handle cmAttributeValueChangeTrap access port QoS shaper');
    }
}
