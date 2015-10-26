<?php

if ($device['os_group'] == 'cisco') {
    echo 'Cisco CEF Switching Path: ';

    $cefs   = array();
    $cefs   = snmpwalk_cache_threepart_oid($device, 'CISCO-CEF-MIB::cefSwitchingStatsEntry', $cefs, 'CISCO-CEF-MIB');
    $polled = time();

    $cefs_query = dbFetchRows('SELECT * FROM `cef_switching` WHERE `device_id` = ?', array($device['device_id']));
    foreach ($cefs_query as $ceftmp) {
        $cef_id           = $device['device_id'].'-'.$ceftmp['entPhysicalIndex'].'-'.$ceftmp['afi'].'-'.$ceftmp['cef_index'];
        $cefs_db[$cef_id] = $ceftmp['cef_switching_id'];
    }

    d_echo($cefs);

    if (is_array($cefs)) {
        if (!is_array($entity_array)) {
            echo 'Caching OIDs: ';
            $entity_array = array();
            echo ' entPhysicalDescr';
            $entity_array = snmpwalk_cache_multi_oid($device, 'entPhysicalDescr', $entity_array, 'ENTITY-MIB');
            echo ' entPhysicalName';
            $entity_array = snmpwalk_cache_multi_oid($device, 'entPhysicalName', $entity_array, 'ENTITY-MIB');
            echo ' entPhysicalModelName';
            $entity_array = snmpwalk_cache_multi_oid($device, 'entPhysicalModelName', $entity_array, 'ENTITY-MIB');
        }

        foreach ($cefs as $entity => $afis) {
            $entity_name = $entity_array[$entity]['entPhysicalName'].' - '.$entity_array[$entity]['entPhysicalModelName'];
            echo "\n$entity $entity_name\n";
            foreach ($afis as $afi => $paths) {
                echo " |- $afi\n";
                foreach ($paths as $path => $cef_stat) {
                    echo ' | |-'.$path.': '.$cef_stat['cefSwitchingPath'];

                    $cef_id = $device['device_id'].'-'.$entity.'-'.$afi.'-'.$path;

                    // if (dbFetchCell("SELECT COUNT(*) FROM `cef_switching` WHERE `device_id` = ? AND `entPhysicalIndex` = ? AND `afi` = ? AND `cef_index` = ?", array($device['device_id'], $entity, $afi, $path)) != "1")
                    if (!isset($cefs_db[$cef_id])) {
                        dbInsert(array('device_id' => $device['device_id'], 'entPhysicalIndex' => $entity, 'afi' => $afi, 'cef_index' => $path, 'cef_path' => $cef_stat['cefSwitchingPath']), 'cef_switching');
                        echo '+';
                    }

                    unset($cefs_db[$cef_id]);

                    $cef_entry = dbFetchRow('SELECT * FROM `cef_switching` WHERE `device_id` = ? AND `entPhysicalIndex` = ? AND `afi` = ? AND `cef_index` = ?', array($device['device_id'], $entity, $afi, $path));

                    $filename = $config['rrd_dir'].'/'.$device['hostname'].'/'.safename('cefswitching-'.$entity.'-'.$afi.'-'.$path.'.rrd');

                    if (!is_file($filename)) {
                        rrdtool_create(
                            $filename,
                            '--step 300 
                            DS:drop:DERIVE:600:0:1000000 
                            DS:punt:DERIVE:600:0:1000000 
                            DS:hostpunt:DERIVE:600:0:1000000 '.$config['rrd_rra']
                        );
                    }

                    // Copy HC to non-HC if they exist
                    if (is_numeric($cef_stat['cefSwitchingHCDrop'])) {
                        $cef_stat['cefSwitchingDrop'] = $cef_stat['cefSwitchingHCDrop'];
                    }

                    if (is_numeric($cef_stat['cefSwitchingHCPunt'])) {
                        $cef_stat['cefSwitchingPunt'] = $cef_stat['cefSwitchingHCPunt'];
                    }

                    if (is_numeric($cef_stat['cefSwitchingHCPunt2Host'])) {
                        $cef_stat['cefSwitchingPunt2Host'] = $cef_stat['cefSwitchingHCPunt2Host'];
                    }

                    // FIXME -- memcached
                    $cef_stat['update']['drop']           = $cef_stat['cefSwitchingDrop'];
                    $cef_stat['update']['punt']           = $cef_stat['cefSwitchingPunt'];
                    $cef_stat['update']['punt2host']      = $cef_stat['cefSwitchingPunt2Host'];
                    $cef_stat['update']['drop_prev']      = $cef_entry['drop'];
                    $cef_stat['update']['punt_prev']      = $cef_entry['punt'];
                    $cef_stat['update']['punt2host_prev'] = $cef_entry['punt2host'];
                    $cef_stat['update']['updated']        = $polled;
                    $cef_stat['update']['updated_prev']   = $cef_entry['updated'];

                    dbUpdate($cef_stat['update'], 'cef_switching', '`device_id` = ? AND `entPhysicalIndex` = ? AND `afi` = ? AND `cef_index` = ?', array($device['device_id'], $entity, $afi, $path));

                    $fields = array(
                        'drop'      => $cef_stat['cefSwitchingDrop'],
                        'punt'      => $cef_stat['cefSwitchingPunt'],
                        'hostpunt'  => $cef_stat['cefSwitchingPunt2Host'],
                    );

                    $ret = rrdtool_update("$filename", $fields);

                    $tags = array('entity' => $entity, 'afi' => $afi, 'index' => $path);
                    influx_update($device,'cefswitching',$tags,$fields);

                    echo "\n";
                }//end foreach
            }//end foreach
        }//end foreach
    }//end if

    // FIXME - need to delete old ones. FIXME REALLY.
    print_r($cefs_db);

    foreach ($cefs_db as $cef_switching_id) {
        dbDelete('cef_switching', '`cef_switching_id` =  ?', array($cef_switching_id));
        echo '-';
    }

    echo "\n";
} //end if
