<?php

$cefs = [];
$cefs = snmpwalk_cache_threepart_oid($device, 'CISCO-CEF-MIB::cefSwitchingPath', $cefs);
d_echo($cefs);

if (is_array($cefs)) {
    if (! is_array($entity_array)) {
        echo 'Caching OIDs: ';
        $entity_array = [];
        echo ' entPhysicalDescr';
        $entity_array = snmpwalk_cache_multi_oid($device, 'entPhysicalDescr', $entity_array, 'ENTITY-MIB');
        echo ' entPhysicalName';
        $entity_array = snmpwalk_cache_multi_oid($device, 'entPhysicalName', $entity_array, 'ENTITY-MIB');
        echo ' entPhysicalModelName';
        $entity_array = snmpwalk_cache_multi_oid($device, 'entPhysicalModelName', $entity_array, 'ENTITY-MIB');
    }

    foreach ($cefs as $entity => $afis) {
        $entity_name = $entity_array[$entity]['entPhysicalName'] . ' - ' . $entity_array[$entity]['entPhysicalModelName'];
        echo "\n$entity $entity_name\n";
        foreach ($afis as $afi => $paths) {
            echo " |- $afi\n";
            foreach ($paths as $path => $path_name) {
                echo ' | |-' . $path . ': ' . $path_name['cefSwitchingPath'] . "\n";
                $cef_exists[$device['device_id']][$entity][$afi][$path] = 1;

                if (dbFetchCell('SELECT COUNT(*) from `cef_switching` WHERE device_id = ? AND entPhysicalIndex = ? AND afi=? AND cef_index=?', [$device['device_id'], $entity, $afi, $path]) != '1') {
                    dbInsert(['device_id' => $device['device_id'], 'entPhysicalIndex' => $entity, 'afi' => $afi, 'cef_path' => $path], 'cef_switching');
                    echo '+';
                }
            }
        }
    }
    unset($cefs);
}//end if

// FIXME - need to delete old ones. FIXME REALLY.
echo "\n";
unset($entity_array);
