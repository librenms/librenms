<?php

if ($device['os_group'] == 'cisco') {
    echo ' CISCO-ENHANCED-MEMORY-POOL: ';

    $array = snmpwalk_cache_multi_oid($device, 'cempMemPoolEntry', null, 'CISCO-ENHANCED-MEMPOOL-MIB');

    if (is_array($array)) {
        foreach ($array as $index => $entry) {
            if (is_numeric($entry['cempMemPoolUsed']) && $entry['cempMemPoolValid'] == 'true') {
                list($entPhysicalIndex) = explode('.', $index);
                $entPhysicalName        = snmp_get($device, 'entPhysicalName.'.$entPhysicalIndex, '-Oqv', 'ENTITY-MIB');

                $descr = $entPhysicalName.' - '.$entry['cempMemPoolName'];

                $descr = str_replace('Cisco ', '', $descr);
                $descr = str_replace('Network Processing Engine', '', $descr);

                discover_mempool($valid_mempool, $device, $index, 'cemp', ucwords($descr), '1', $entPhysicalIndex, null);
            }
        }
    }
}//end if
