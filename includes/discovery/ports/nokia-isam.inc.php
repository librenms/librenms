<?php

// Nokia ISAM has ports that can be accessed in other SNMP context.
// IHUB contains the ports of the NT cards ans backplane ports from NT to line cards

// Store the current context and set context to the extra context(s) we want to walk
$old_context_name = $device['context_name'];
$device['context_name'] = 'ihub';

$isam_port_stats = snmpwalk_cache_oid($device, 'ifDescr', $isam_port_stats, 'IF-MIB', null, $descrSnmpFlags);
$isam_port_stats = snmpwalk_cache_oid($device, 'ifName', $isam_port_stats, 'IF-MIB');
$isam_port_stats = snmpwalk_cache_oid($device, 'ifAlias', $isam_port_stats, 'IF-MIB');
$isam_port_stats = snmpwalk_cache_oid($device, 'ifType', $isam_port_stats, 'IF-MIB', null, $typeSnmpFlags);
$isam_port_stats = snmpwalk_cache_oid($device, 'ifOperStatus', $isam_port_stats, 'IF-MIB', null, $operStatusSnmpFlags);

$port_stats = array_merge($port_stats, $isam_port_stats);

$device['context_name'] = $old_context_name;
unset($old_context_name);
unset($isam_ports_stats);
