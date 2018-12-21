#!/usr/bin/env php
<?php

/**
 * test-adva-snmptrap.php
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
 * Test script that runs through all of the Adva specific SNMP trap
 * handlers found ~/LibreNMS/Snmptrap/Handlers.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 KanREN, Inc.
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

$init_modules = ['laravel', 'alerts-cli']; // so I don't have to rebase yet
require '/opt/librenms/includes/init.php';

$options = getopt('t:h:d::');

if (set_debug(isset($options['d']))) {
    echo "DEBUG!\n";
}

$time = 2;
if (isset($options['t'])) {
    $time = $options['t'];
}

if (!isset($options['h'])) {
    echo ("
Usage:

    test-adva-snmptrap.php -h <hostname> [options]

Options:

    -d debug mode.
    -h followed by the hostname or IP address is required.
    -t time to wait in seconds between handler tests. Defaults to 2 seconds.\n\n");

    exit(1);
}
$hostname = $options['h'];

echo ("\nThis test script will sequentually send pre-created trap data to the trap handling system using the hostname given with a 2 second
delay between each trap. The expected log text will display below, compare with the eventlog in the UI for the device given. \n\n");

/*
 * Object Creation
 */

echo ("The following traps will test AdvaObjectCreation.php: \n\n");

// User creation trap

$userCreate = "$hostname
UDP: [192.168.1.1]:36570->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:40:34.67
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmObjectCreationTrap
CM-SECURITY-MIB::cmSecurityUserPrivLevel.\"test-trap-user\".false superuser
CM-SECURITY-MIB::cmSecurityUserLoginTimeout.\"test-trap-user\".false 15
CM-SECURITY-MIB::cmSecurityUserName.\"test-trap-user\".false trap-test-user
CM-SECURITY-MIB::cmSecurityUserComment.\"test-trap-user\".false Remote User";

$trap = new \LibreNMS\Snmptrap\Trap($userCreate);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

echo ("User object trap-test-user created.\n");
sleep($time);

// LAG creation trap

$lagCreate = "$hostname
UDP: [192.168.1.1]:36570->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:43:37.24
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmObjectCreationTrap
IEEE8023-LAG-MIB::dot3adAggCollectorMaxDelay.9 50
IEEE8023-LAG-MIB::dot3adAggActorSystemPriority.9 32768
IEEE8023-LAG-MIB::dot3adAggActorAdminKey.9 32768
F3-LAG-MIB::f3LagProtocols.1.1 true
F3-LAG-MIB::f3LagDiscardWrongConversation.1.1 false
F3-LAG-MIB::f3LagFrameDistAlgorithm.1.1 activeStandby
F3-LAG-MIB::f3LagMode.1.1 active-standby
F3-LAG-MIB::f3LagLacpControl.1.1 true
F3-LAG-MIB::f3LagCcmDefectsDetectionEnabled.1.1 false
F3-LAG-MIB::f3LagName.1.1
F3-LAG-MIB::f3LagEntry.14.1.1 \"B0 00 \"";

$trap = new \LibreNMS\Snmptrap\Trap($lagCreate);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

echo ("LAG 1 created.\n");
sleep($time);

/*
 * Object Deletion Traps
 */

echo ("\nThe following traps will test AdvaObjectDeletion.php:\n\n");

// User deletion trap

$userDelete = "$hostname
UDP: [192.168.1.1]:36570->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:41:21.00
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmObjectDeletionTrap
CM-SECURITY-MIB::cmSecurityUserName.\"test-trap-user\".false test-trap-user";

$trap = new \LibreNMS\Snmptrap\Trap($text);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

echo ("User object test-trap-user deleted.\n");
sleep(time);

// LAG deletion trap

$lagDelete = "$hostname
UDP: [192.168.1.1]:36570->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:48:44.97
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmObjectDeletionTrap
F3-LAG-MIB::f3LagIndex.1.1 1";

$trap = new \LibreNMS\Snmptrap\Trap($lagDelete);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

echo ("LAG 1 deleted.\n");
sleep($time);

// LAG member port deletion trap

$lagPortDelete = "$hostname
UDP: [192.168.1.1]:36570->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:48:44.89
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmObjectDeletionTrap
F3-LAG-MIB::f3LagPortIndex.1.1.1 1";

$trap = new \LibreNMS\Snmptrap\Trap($lagPortDelete);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

echo ("LAG member port 1 removed from LAG 1-1. \n");
sleep($time);

// Flow deletion trap

$flowDelete = "$hostname
UDP: [192.168.1.1]:24402->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 1:2:20:28.99
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmObjectDeletionTrap
CM-FACILITY-MIB::cmFlowIndex.1.1.1.4.1 1";

$trap = new \LibreNMS\Snmptrap\Trap($flowDelete);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

echo ("Flow 1-1-1-4-1 deleted. \n");
sleep($time);

/*
 * Configuration attribute change traps
 */

echo ("\nThe following traps will test AdvaAttributeChange.php:\n\n");

// Syslog modified traps

$syslogIpv4 = "$hostname
UDP: [192.168.1.1]:36570->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:20:00:32.40
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-SYSTEM-MIB::sysLogIpVersion.1 ipv4
CM-SYSTEM-MIB::sysLogIpAddress.1 10.255.255.2
CM-SYSTEM-MIB::sysLogPort.1 514";

$trap = new \LibreNMS\Snmptrap\Trap($syslogIpv4);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

echo ("Syslog server 1 IP version set to ipv4. \n");
echo ("Syslog server 1 IP address changed to 10.255.255.2. \n");
echo ("Syslog server 1 IP address changed to 514. \n");
sleep($time);

$syslogIpv6 = "$hostname
UDP: [192.168.1.1]:36570->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:20:01:13.06
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-SYSTEM-MIB::sysLogIpv6Addr.1 2001:0:0:0:0:0:0:2
CM-SYSTEM-MIB::sysLogIpVersion.1 ipv6";

$trap = new \LibreNMS\Snmptrap\Trap($syslogIpv6);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

echo ("Syslog server 1 IP version set to ipv6. \n");
echo ("Syslog server 1 IP address changed to 2001:0:0:0:0:0:0:2. \n");
sleep($time);

// ACL modified trap

$aclEntry = "$hostname
UDP: [192.168.1.1]:36570->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:20:02:16.05
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-SYSTEM-MIB::aclEntryEnabled.5 false";

$trap = new \LibreNMS\Snmptrap\Trap($aclEntry);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

echo ("ACL 5 modified \n");
sleep($time);

// Banner modified trap

$banner = "$hostname
UDP: [192.168.1.1]:36570->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:20:03:37.17
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-SYSTEM-MIB::securityBanner.0 Banner Text";

$trap = new \LibreNMS\Snmptrap\Trap($banner);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

echo ("MOTD/Banner modified \n");
sleep($time);

// Time and NTP modified traps

$dstDisabled = "$hostname
UDP: [192.168.1.1]:36570->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:20:05:27.05
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
F3-TIMEZONE-MIB::f3TimeZoneDstControlEnabled.0 false";

$trap = new \LibreNMS\Snmptrap\Trap($dstDisabled);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

$dstEnabled = "$hostname
UDP: [192.168.1.1]:36570->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:20:05:27.05
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
F3-TIMEZONE-MIB::f3TimeZoneDstControlEnabled.0 true";

$trap = new \LibreNMS\Snmptrap\Trap($dstEnabled);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

echo ("Daylight Savings Time disabled.\n");
echo ("Daylight Savings Time enabled.\n");
sleep($time);

$utcOffset = "$hostname
UDP: [192.168.1.1]:36570->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:20:06:23.89
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
F3-TIMEZONE-MIB::f3TimeZoneUtcOffset.0 -05:00";

$trap = new \LibreNMS\Snmptrap\Trap($utcOffset);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

echo ("UTC offset (timezone) change to -05:00\n");
sleep($time);

$timeSrc = "$hostname
UDP: [192.168.1.1]:36570->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:20:13:50.65
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-SYSTEM-MIB::sysTimeOfDayType.0 ntp";

$trap = new \LibreNMS\Snmptrap\Trap($timeSrc);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

echo ("Time source set to ntp \n");
sleep($time);

$ntpPrimary = "$hostname
UDP: [192.168.1.1]:36570->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:20:15:10.32
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-SYSTEM-MIB::ntpPrimaryServer.0 10.255.255.3
CM-SYSTEM-MIB::ntpPrimaryServerIpVersion.0 ipv4";

$trap = new \LibreNMS\Snmptrap\Trap($ntpPrimary);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

$ntpSecondary = "$hostname
UDP: [192.168.1.1]:36570->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:20:15:57.25
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-SYSTEM-MIB::ntpBackupServer.0 10.255.255.4
CM-SYSTEM-MIB::ntpBackupServerIpVersion.0 ipv4";

$trap = new \LibreNMS\Snmptrap\Trap($ntpSecondary);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

echo ("Primary NTP server IP 10.255.255.3\n");
echo ("Backup NTP server IP 10.255.255.4\n");
sleep($time);

// Authentication server modified traps

$authServer = "$hostname
UDP: [192.168.1.1]:36570->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:20:17:11.27
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-SECURITY-MIB::cmRemoteAuthServerAccountingPort.3 1810
CM-SECURITY-MIB::cmRemoteAuthServerPort.3 1811
CM-SECURITY-MIB::cmRemoteAuthServerIpAddress.3 10.255.255.5
CM-SECURITY-MIB::cmRemoteAuthServerSecret.3 *****
CM-SECURITY-MIB::cmRemoteAuthServerEnabled.3 true";

$trap = new \LibreNMS\Snmptrap\Trap($authServer);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

echo ("Authentication server enabled.\n");
echo ("Authentication server 3 secret changed\n");
echo ("Authentication server 3 IP changed to 10.255.255.5.\n");
sleep($time);

// Network Element card name and CLI prefix modified traps

$neName = "$hostname
UDP: [192.168.1.1]:36570->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:20:19:16.23
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-ENTITY-MIB::neName.1 adva-test-1
CM-ENTITY-MIB::neCmdPromptPrefix.1 adva-test-1";

$trap = new \LibreNMS\Snmptrap\Trap($neName);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

echo ("Network Element name changed to adva-test-1.\n");
echo ("Network Element prompt changed to adva-test-1.\n");
sleep($time);

// SNMP Dying Gasp Enable/Disable traps

$gaspEnable = "$hostname
UDP: [192.168.1.1]:36570->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:20:21:07.75
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-ENTITY-MIB::ethernetNTEGE114ProCardSnmpDyingGaspEnabled.1.1.1 true";

$trap = new \LibreNMS\Snmptrap\Trap($gaspEnable);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

echo ("SNMP Dying Gasp is enabled.\n");

$gaspDisable = "$hostname
UDP: [192.168.1.1]:36570->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:20:21:07.75
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-ENTITY-MIB::ethernetNTEGE114ProCardSnmpDyingGaspEnabled.1.1.1 false";

$trap = new \LibreNMS\Snmptrap\Trap($gaspDisable);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

echo ("SNMP Dying Gasp is disabled.\n");
sleep($time);

// Network port modified traps

$netPort = "$hostname
UDP: [192.168.1.1]:36570->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:20:26:42.30
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-FACILITY-MIB::cmEthernetNetPortConfigSpeed.1.1.1.2 speed-auto-100MB-full
CM-FACILITY-MIB::cmEthernetNetPortMediaType.1.1.1.2 copper
CM-FACILITY-MIB::cmEthernetNetPortMDIXType.1.1.1.2 crossed
CM-FACILITY-MIB::cmEthernetNetPortAutoDiagEnabled.1.1.1.2 false
CM-FACILITY-MIB::cmEthernetNetPortAdminState.1.1.1.2 in-service
CM-FACILITY-MIB::cmEthernetNetPortMTU.1.1.1.2 9000
CM-FACILITY-MIB::cmEthernetNetPortConfigSpeed.1.1.1.2 speed-auto-100MB-full";

$trap = new \LibreNMS\Snmptrap\Trap($netPort);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

echo ("Network Port 1-1-1-2 changed speed to speed-auto-100MB-full.\n");
echo ("Network Port 1-1-1-2 changed media to copper.\n");
echo ("Network Port 1-1-1-2 changed MDIX to crossed.\n");
echo ("Network Port 1-1-1-2 AutoDiagnostic disabled.\n");
echo ("Network Port 1-1-1-2 administrative state changed to in-service.\n");
echo ("Network Port 1-1-1-2 MTU changed to 9000 bytes.\n");
sleep($time);

// Access Port modified traps

$accPort = "$hostname
UDP: [192.168.1.1]:36570->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:20:28:15.63
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-FACILITY-MIB::cmEthernetAccPortA2NPushPVIDEnabled.1.1.1.4 false
CM-FACILITY-MIB::cmEthernetAccPortSvcType.1.1.1.4 epl
CM-FACILITY-MIB::cmEthernetAccPortMediaType.1.1.1.4 fiber
CM-FACILITY-MIB::cmEthernetAccPortAutoDiagEnabled.1.1.1.4 false
CM-FACILITY-MIB::cmEthernetAccPortConfigSpeed.1.1.1.4 speed-auto-1000MB-full
CM-FACILITY-MIB::cmEthernetAccPortMDIXType.1.1.1.4 not-applicable
CM-FACILITY-MIB::cmEthernetAccPortMTU.1.1.1.4 9000
CM-FACILITY-MIB::cmEthernetAccPortA2nSwapPriorityVIDEnabled.1.1.1.4 false
CM-FACILITY-MIB::cmEthernetAccPortAdminState.1.1.1.4 maintenance
IF-MIB::ifAlias.4 Shado's Desk";

$trap = new \LibreNMS\Snmptrap\Trap($accPort);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

echo ("Access Port 1-1-1-4 changed speed to speed-auto-1000MB-full.\n");
echo ("Access Port 1-1-1-4 changed media to fiber.\n");
echo ("Access Port 1-1-1-4 changed MDIX to not-applicable.\n");
echo ("Access Port 1-1-1-4 MTU changed to 9000 bytes.\n");
echo ("Access Port 1-1-1-4 administrative state changed to maintenance.\n");
sleep($time);

// Flow modified traps

$flow = "$hostname
UDP: [192.168.1.1]:36570->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:19:52:21.61
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-FACILITY-MIB::cmFlowN2AEIR.1.1.1.4.1 0
CM-FACILITY-MIB::cmFlowN2ACIRHi.1.1.1.4.1 0";

$trap = new \LibreNMS\Snmptrap\Trap($flow);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

echo ("Access Flow 1-1-1-4-1 modified.\n");
sleep($time);

// QoS modified traps

$flowPolicer = "$hostname
UDP: [192.168.1.1]:36570->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 26:20:32:44.31
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-FACILITY-MIB::cmQosFlowPolicerCIRLo.1.1.1.3.1.1.1 9856000";

$trap = new \LibreNMS\Snmptrap\Trap($flowPolicer);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

echo ("QoS on flow 1-1-1-3-1 modified.\n");
sleep($time);

$accShaper = "$hostname
UDP: [192.168.1.1]:24402->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 4:2:39:15.10
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmAttributeValueChangeTrap
CM-FACILITY-MIB::cmAccPortQosShaperCIRLo.1.1.1.3.1 120000000";

$trap = new \LibreNMS\Snmptrap\Trap($accShaper);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

echo ("Shaper modified on access port 1-1-1-3-1 modified.\n");
sleep($time);

/*
 * This section test AdvaSnmpDyingGasp.php
 */

echo ("\nThe following trpas with test AdvaSnmpDyingGaspTrap.php:\n\n");

$dyingGasp = "$hostname
UDP: [192.168.1.1]:53407->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:0:04:06.96
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmSnmpDyingGaspTrap";

$trap = new \LibreNMS\Snmptrap\Trap($dyingGasp);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

echo ("Dying Gasp received\n");
sleep($time);

/*
 * The following section tests AdvaStateChange.php
 */

// Network interface state changes

echo ("\nThe following traps will test AdvaStateChangeTrap.php:\n\n");

$neState = "$hostname
UDP: [192.168.1.1]:24402->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:0:04:09.81
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmStateChangeTrap
CM-FACILITY-MIB::cmEthernetNetPortAdminState.1.1.1.2 maintenance
CM-FACILITY-MIB::cmEthernetNetPortOperationalState.1.1.1.2 outage
CM-FACILITY-MIB::cmEthernetNetPortSecondaryState.1.1.1.2 \"52 00 00 \"
IF-MIB::ifName.2 NETWORK PORT-1-1-1-2";

$trap = new \LibreNMS\Snmptrap\Trap($neState);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

echo ("Port state change: NETWORK PORT-1-1-1-2 Admin State: maintenance Operational State: outage\n");
sleep($time);

$accState = "$hostname
UDP: [192.168.1.1]:24402->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:0:07:42.66
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmStateChangeTrap
CM-FACILITY-MIB::cmEthernetAccPortAdminState.1.1.1.3 maintenance
CM-FACILITY-MIB::cmEthernetAccPortOperationalState.1.1.1.3 normal
CM-FACILITY-MIB::cmEthernetAccPortSecondaryState.1.1.1.3 \"42 00 00 \"
IF-MIB::ifName.3 ACCESS PORT-1-1-1-3";

$trap = new \LibreNMS\Snmptrap\Trap($accState);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

echo ("Port state change: ACCESS PORT-1-1-1-3 Admin State: maintenance Operational State: normal\n");
sleep($time);

$flowState = "$hostname
UDP: [192.168.1.1]:24402->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:0:07:42.70
SNMPv2-MIB::snmpTrapOID.0 CM-SYSTEM-MIB::cmStateChangeTrap
CM-FACILITY-MIB::cmFlowAdminState.1.1.1.3.1 management
CM-FACILITY-MIB::cmFlowOperationalState.1.1.1.3.1 normal
CM-FACILITY-MIB::cmFlowSecondaryState.1.1.1.3.1 \"40 00 00 \"";

$trap = new \LibreNMS\Snmptrap\Trap($flowState);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

echo ("Flow state change: 1-1-1-3-1 Admin State: management Operational State: normal\n");
sleep($time);

/*
 * This section tests AdvaSysAlmtrap.php
 */

echo ("\nThe following traps will test AdvaSysAlmTrap.php:\n\n");

$sysAlarm = "$hostname
UDP: [192.168.1.1]:24402->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:0:15:22.68
SNMPv2-MIB::snmpTrapOID.0 CM-ALARM-MIB::cmSysAlmTrap
CM-ALARM-MIB::cmAlmIndex.5 5
CM-ALARM-MIB::cmSysAlmNotifCode.5 minor
CM-ALARM-MIB::cmSysAlmType.5 primntpsvrFailed
CM-ALARM-MIB::cmSysAlmSrvEff.5 nonServiceAffecting
CM-ALARM-MIB::cmSysAlmTime.5 2018-12-10,11:28:20.0,-6:0
CM-ALARM-MIB::cmSysAlmLocation.5 nearEnd
CM-ALARM-MIB::cmSysAlmDirection.5 receiveDirectionOnly
CM-ALARM-MIB::cmSysAlmDescr.5 \"Primary NTP Server Failed\"
CM-ALARM-MIB::cmSysAlmObject.5 SNMPv2-MIB::system
CM-ALARM-MIB::cmSysAlmObjectName.5 SYSTEM";

$trap = new \LibreNMS\Snmptrap\Trap($sysAlarm);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

echo ("System Alarm: Primary NTP Server Failed Status: minor\n");
sleep($time);

/*
 * This section tests AdvaNetThresholdCrossingAlert.php
 */

echo ("\nThe following traps will test AdvaNetThresholdCrossingAlert.php:\n");

$netAlarm = "$hostname
UDP: [192.168.1.1]:24402->[10.0.0.1]:162
DISMAN-EVENT-MIB::sysUpTimeInstance 0:0:32:12.74
SNMPv2-MIB::snmpTrapOID.0 CM-PERFORMANCE-MIB::cmEthernetNetPortThresholdCrossingAlert
CM-PERFORMANCE-MIB::cmEthernetNetPortThresholdIndex.1.1.1.2.1.37 37
CM-PERFORMANCE-MIB::cmEthernetNetPortThresholdInterval.1.1.1.2.1.37 interval-15min
CM-PERFORMANCE-MIB::cmEthernetNetPortThresholdVariable.1.1.1.2.1.37 CM-PERFORMANCE-MIB::cmEthernetNetPortStatsUAS.1.1.1.2.1
CM-PERFORMANCE-MIB::cmEthernetNetPortThresholdValueLo.1.1.1.2.1.37 10
CM-PERFORMANCE-MIB::cmEthernetNetPortThresholdValueHi.1.1.1.2.1.37 0
CM-PERFORMANCE-MIB::cmEthernetNetPortThresholdMonValue.1.1.1.2.1.37 10
IF-MIB::ifName.2 NETWORK PORT-1-1-1-2";

$trap = new \LibreNMS\Snmptrap\Trap($netAlarm);
\LibreNMS\Snmptrap\Dispatcher::handle($trap);

echo ("NETWORK PORT-1-1-1-2 threshold exceeded for interval-15min. Threshold OID is CM-PERFORMANCE-MIB::cmEthernetNetPortStatsUAS.1.1.1.2.1.\n\n");
