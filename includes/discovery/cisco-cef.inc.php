<?php

use Illuminate\Support\Facades\Log;
use LibreNMS\Util\ProgressList;

$cefs = SnmpQuery::hideMib()->walk('CISCO-CEF-MIB::cefSwitchingPath')->table(3);
d_echo($cefs);

if (is_array($cefs)) {
    if (! isset($entity_array) || ! is_array($entity_array)) {
        $progress = ProgressList::start('Caching OIDs');
        $entity_array = [];
        $progress->add('entPhysicalDescr');
        $entity_array = snmpwalk_cache_multi_oid($device, 'entPhysicalDescr', $entity_array, 'ENTITY-MIB');
        $progress->add('entPhysicalName');
        $entity_array = snmpwalk_cache_multi_oid($device, 'entPhysicalName', $entity_array, 'ENTITY-MIB');
        $progress->add('entPhysicalModelName');
        $entity_array = snmpwalk_cache_multi_oid($device, 'entPhysicalModelName', $entity_array, 'ENTITY-MIB');
        $progress->end();
    }

    foreach ($cefs as $entity => $afis) {
        $entity_name = $entity_array[$entity]['entPhysicalName'] . ' - ' . $entity_array[$entity]['entPhysicalModelName'];
        Log::info("\n$entity $entity_name");
        foreach ($afis as $afi => $paths) {
            Log::info(" |- $afi");
            foreach ($paths as $path => $path_name) {
                Log::info(' | |-' . $path . ': ' . $path_name['cefSwitchingPath']);
                $cef_exists[$device['device_id']][$entity][$afi][$path] = 1;

                if (dbFetchCell('SELECT COUNT(*) from `cef_switching` WHERE device_id = ? AND entPhysicalIndex = ? AND afi=? AND cef_index=?', [$device['device_id'], $entity, $afi, $path]) != '1') {
                    dbInsert(['device_id' => $device['device_id'], 'entPhysicalIndex' => $entity, 'afi' => $afi, 'cef_path' => $path], 'cef_switching');
                    Log::info('+', ['nlb' => true]);
                }
            }
        }
    }
    unset($cefs);
}//end if

// FIXME - need to delete old ones. FIXME REALLY.
Log::info('');
unset($entity_array);
