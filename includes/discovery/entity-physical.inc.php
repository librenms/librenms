<?php

if ($config['enable_inventory']) {
    // Legacy entPhysical - junos/timos/cisco
    include 'includes/discovery/entity-physical/entity-physical.inc.php';

    // Cisco CIMC
    if ($device['os'] == 'cimc') {
        include 'includes/discovery/entity-physical/cimc.inc.php';
    }

    // Delete any entries that have not bee accounted for.
    $sql = 'SELECT * FROM `entPhysical` WHERE `device_id` = ?';
    foreach (dbFetchRows($sql, array($device['device_id'])) as $test) {
        $id = $test['entPhysicalIndex'];
        if (!$valid[$id]) {
            echo '-';
            dbDelete('entPhysical', 'entPhysical_id = ?', array ($test['entPhysical_id']));
        }
    }
} else {
    echo 'Disabled!';
}//end if
