<?php

use LibreNMS\Config;

if (Config::get('enable_inventory')) {
    // Legacy entPhysical - junos/timos/cisco
    include 'includes/discovery/entity-physical/entity-physical.inc.php';

    if (file_exists(Config::get('install_dir') . "/includes/discovery/entity-physical/{$device['os']}.inc.php")) {
        include Config::get('install_dir') . "/includes/discovery/entity-physical/{$device['os']}.inc.php";
    }

    // Delete any entries that have not been accounted for.
    $sql = 'SELECT * FROM `entPhysical` WHERE `device_id` = ?';
    foreach (dbFetchRows($sql, [$device['device_id']]) as $test) {
        $id = $test['entPhysicalIndex'];
        if (! $valid[$id]) {
            echo '-';
            dbDelete('entPhysical', 'entPhysical_id = ?', [$test['entPhysical_id']]);
        }
    }
    unset(
        $sql,
        $test,
        $valid
    );
} else {
    echo 'Disabled!';
}//end if
