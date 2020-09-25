<?php

$pre_cache['siteboss_mibs'] = 'SITEBOSS-530-STD-MIB:SITEBOSS-550-STD-MIB';

// caches device OID prefix from sysObjectID
echo 'sysObjectID ';
$oid = snmp_get($device, 'sysObjectID.0', '-OQvn', 'SNMPv2-MIB');
$prefix_len = strpos($oid, '3052') + 5;
$suffix_len = strpos(substr($oid, $prefix_len), '.');
$pre_cache['oid_prefix'] = substr($oid, 0, $prefix_len + $suffix_len);

echo 'esPointTable ';
$pre_cache['esPointTable'] = snmpwalk_cache_multi_oid($device, 'esPointTable', [], $pre_cache['siteboss_mibs'], null, '-OQUbs');

unset($oid, $prefix_len, $suffix_len);
