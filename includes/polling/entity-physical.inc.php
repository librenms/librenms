<?php

if ($device['os'] == 'ios') {
    echo "Cisco Cat6xxx/76xx Crossbar : \n";

    $mod_stats  = snmpwalk_cache_oid($device, 'cc6kxbarModuleModeTable', array(), 'CISCO-CAT6K-CROSSBAR-MIB');
    $chan_stats = snmpwalk_cache_oid($device, 'cc6kxbarModuleChannelTable', array(), 'CISCO-CAT6K-CROSSBAR-MIB');
    $chan_stats = snmpwalk_cache_oid($device, 'cc6kxbarStatisticsTable', $chan_stats, 'CISCO-CAT6K-CROSSBAR-MIB');

    foreach ($mod_stats as $index => $entry) {
        $group = 'c6kxbar';
        foreach ($entry as $key => $value) {
            $subindex = null;
            $entPhysical_state[$index][$subindex][$group][$key] = $value;
        }
    }

    foreach ($chan_stats as $index => $entry) {
        list($index,$subindex) = explode('.', $index, 2);
        $group                 = 'c6kxbar';
        foreach ($entry as $key => $value) {
            $entPhysical_state[$index][$subindex][$group][$key] = $value;
        }

        $rrd_name = array('c6kxbar', $index, $subindex);
        $rrd_def = array(
            'DS:inutil:GAUGE:600:0:100',
            'DS:oututil:GAUGE:600:0:100',
            'DS:outdropped:DERIVE:600:0:125000000000',
            'DS:outerrors:DERIVE:600:0:125000000000',
            'DS:inerrors:DERIVE:600:0:125000000000'
        );

        $fields = array(
            'inutil'      => $entry['cc6kxbarStatisticsInUtil'],
            'oututil'     => $entry['cc6kxbarStatisticsOutUtil'],
            'outdropped'  => $entry['cc6kxbarStatisticsOutDropped'],
            'outerrors'   => $entry['cc6kxbarStatisticsOutErrors'],
            'inerrors'    => $entry['cc6kxbarStatisticsInErrors'],
        );

        $tags = compact('index', 'subindex', 'rrd_name', 'rrd_def');
        data_update($device, 'c6kxbar', $tags, $fields);
    }//end foreach

    // print_r($entPhysical_state);
}//end if

// Set Entity state
foreach (dbFetch('SELECT * FROM `entPhysical_state` WHERE `device_id` = ?', array($device['device_id'])) as $entity) {
    if (!isset($entPhysical_state[$entity['entPhysicalIndex']][$entity['subindex']][$entity['group']][$entity['key']])) {
        dbDelete(
            'entPhysical_state',
            '`device_id` = ? AND `entPhysicalIndex` = ? AND `subindex` = ? AND `group` = ? AND `key` = ?',
            array(
             $device['device_id'],
             $entity['entPhysicalIndex'],
             $entity['subindex'],
             $entity['group'],
             $entity['key'],
            )
        );
    } else {
        if ($entPhysical_state[$entity['entPhysicalIndex']][$entity['subindex']][$entity['group']][$entity['key']] != $entity['value']) {
            echo 'no match!';
        }

        unset($entPhysical_state[$entity['entPhysicalIndex']][$entity['subindex']][$entity['group']][$entity['key']]);
    }
}//end foreach

// End Set Entity Attrivs
// Delete Entity state
foreach ($entPhysical_state as $epi => $entity) {
    foreach ($entity as $subindex => $si) {
        foreach ($si as $group => $ti) {
            foreach ($ti as $key => $value) {
                dbInsert(array('device_id' => $device['device_id'], 'entPhysicalIndex' => $epi, 'subindex' => $subindex, 'group' => $group, 'key' => $key, 'value' => $value), 'entPhysical_state');
            }
        }
    }
} // End Delete Entity state

// Cisco CIMC
if ($device['os'] == 'cimc') {
    $module = 'Cisco-CIMC';
    $component = new LibreNMS\Component();
    $components = $component->getComponents($device['device_id'], array('type'=>$module));

    // We only care about our device id.
    $components = $components[$device['device_id']];

    // Only collect SNMP data if we have enabled components
    if (count($components > 0)) {
        // Let's gather some data..
        $tblUCSObjects = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.719.1', 0);

        // Make sure we have an array of data before we try to iterate over it
        if (is_array($tblUCSObjects)) {
            // First, let's extract any active faults, we will use them later.
            $faults = array();
            foreach ($tblUCSObjects as $oid => $data) {
                if (strstr($oid, '1.3.6.1.4.1.9.9.719.1.1.1.1.5.')) {
                    $id = substr($oid, 30);
                    $fobj = $tblUCSObjects['1.3.6.1.4.1.9.9.719.1.1.1.1.5.'.$id];
                    $fobj = preg_replace('/^sys/', '/sys', $fobj);
                    $faults[$fobj] = $tblUCSObjects['1.3.6.1.4.1.9.9.719.1.1.1.1.11.'.$id];
                }
            }

            foreach ($components as &$array) {
                /*
                 * Because our discovery module was nice and stored the OID we need to
                 * poll for status, this is quite straight forward.
                 *
                 * Was the status oid found in the list?
                 */
                if (isset($tblUCSObjects[$array['statusoid']])) {
                    if ($tblUCSObjects[$array['statusoid']] != 1) {
                        // Yes, report an error
                        $array['status'] = 2;
                        $array['error'] = "Error Operability Code: ".$tblUCSObjects[$array['statusoid']]."\n";
                    } else {
                        // No, unset any errors that may exist.
                        $array['status'] = 0;
                        $array['error'] = '';
                    }
                }

                // if the type is chassis, we may have to add generic chassis faults
                if ($array['hwtype'] == 'chassis') {
                    // See if there are any errors on this chassis.
                    foreach ($faults as $key => $value) {
                        if (strstr($key, $array['label'])) {
                            // The fault is on this chassis.
                            $array['status'] = 2;
                            $array['error'] .= $value."\n";
                        }
                    }
                }

                // Print some debugging
                if ($array['status'] == 0) {
                    d_echo($array['label']." - Ok\n");
                } else {
                    d_echo($array['label']." - ".$array['error']."\n");
                }
            } // End foreach components
            // Write the Components back to the DB.
            $component->setComponentPrefs($device['device_id'], $components);
        } // End is_array
        echo "\n";
    } // End if not error
}

echo "\n";
