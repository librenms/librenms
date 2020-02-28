<?php

/*
 * forcepoint-os.inc.php
 * LibreNMS storage poller module for forcepoint
 */

if (!is_array($storage_cache['Storage'])) {
    $storage_cache['Storage'] = snmpwalk_cache_oid($device, 'fwDiskStatsEntry', array(), 'STONESOFT-FIREWALL-MIB');
    d_echo($storage_cache);
}

$entry = $storage_cache['Storage'][$storage['storage_index']];

$storage['units'] = 1024;
$storage['size'] = $entry['fwPartitionSize'];
$storage['used'] = $entry['fwPartitionUsed'];
$storage['free'] = $entry['fwPartitionAvail'];
