<?php
/*
 * Called from includes/polling/storage.inc.php
 */

if (isset($storage) and isset($device)) {
    $oids = array('fwPartitionSize.' . $storage['storage_index'], 'fwPartitionAvail.' . $storage['storage_index'], 'fwPartitionUsed.' . $storage['storage_index']);
    $allEntries = snmp_get_multi($device, $oids, '-OQUs', 'STONESOFT-FIREWALL-MIB');
    $entry = array_shift($allEntries);
    $storage['size'] = $entry['fwPartitionSize']*1024;
    $storage['free'] = $entry['fwPartitionAvail']*1024;
    $storage['used'] = $entry['fwPartitionUsed']*1024;
    $storage['units'] = 1;

    unset ($oids, $allEntries, $entry);
}
