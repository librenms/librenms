<?php

// caches device OID prefix from sysObjectID
echo 'oid_prefix ';
$prefix_len = strpos($device['sysObjectID'], '3052') + 5;
$suffix_len = strpos(substr($device['sysObjectID'], $prefix_len), '.');
$pre_cache['oid_prefix'] = substr($device['sysObjectID'], 0, $prefix_len + $suffix_len);
echo $pre_cache['oid_prefix'] . ' ';

echo 'esPointTable ';
$pre_cache['esPointTable'] = snmpwalk_cache_multi_oid($device, 'esPointTable', [], 'SITEBOSS-550-STD-MIB', null, '-OQUbs');

unset($prefix_len, $suffix_len);
