<?php

// Nokia ISAM has ports that can be accessed in other SNMP context.
// IHUB contains the ports of the NT cards ans backplane ports from NT to line cards

// Store the current context and set context to the extra context(s) we want to walk
$old_context_name = $device['context_name'];
$device['context_name'] = 'ihub';

$port_stats = snmpwalk_cache_oid($device, 'ifDescr', $port_stats, 'IF-MIB', null, $descrSnmpFlags);
$port_stats = snmpwalk_cache_oid($device, 'ifName', $port_stats, 'IF-MIB');
$port_stats = snmpwalk_cache_oid($device, 'ifAlias', $port_stats, 'IF-MIB');
$port_stats = snmpwalk_cache_oid($device, 'ifType', $port_stats, 'IF-MIB', null, $typeSnmpFlags);
$port_stats = snmpwalk_cache_oid($device, 'ifOperStatus', $port_stats, 'IF-MIB', null, $operStatusSnmpFlags);

$device['context_name'] = $old_context_name;
unset($old_context_name);
