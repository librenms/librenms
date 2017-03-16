<?php

$hardware = trim(snmp_get($device, "hmPNIOOrderID.0", "-OQv", "HMPRIV-MGMT-SNMP-MIB"), '"');
$version = trim(snmp_get($device, "hmPNIOSoftwareRelease.0", "-OQv", "HMPRIV-MGMT-SNMP-MIB"), '"');
$serial = trim(snmp_get($device, "hmSysGroupSerialNum.1", "-OQv", "HMPRIV-MGMT-SNMP-MIB"), '"');
$hostname = trim(snmp_get($device, "sysName.0", "-OQv", "SNMPv2-MIB"), '"');
$cpu_usage = trim(snmp_get($device, "hmCpuUtilization.0", "-OQv", "HMPRIV-MGMT-SNMP-MIB"), '"');
