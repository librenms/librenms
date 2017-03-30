<?php

$hardware = snmp_get($device, "hmPNIOOrderID.0", "-OQv", "HMPRIV-MGMT-SNMP-MIB");
$version = snmp_get($device, "hmPNIOSoftwareRelease.0", "-OQv", "HMPRIV-MGMT-SNMP-MIB");
$serial = snmp_get($device, "hmSysGroupSerialNum.1", "-OQv", "HMPRIV-MGMT-SNMP-MIB");
$hostname = snmp_get($device, "sysName.0", "-OQv", "SNMPv2-MIB");
$cpu_usage = snmp_get($device, "hmCpuUtilization.0", "-OQv", "HMPRIV-MGMT-SNMP-MIB");
