<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2017 Aldemir Akpinar <https://github.com/aldemira/>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
header('Content-type: application/json');

if (is_admin() === false) {
    die('ERROR: You need to be admin');
}

if (!is_numeric($_POST['parent_id'])) {
    echo 'Wrong Parent host ID!';
    exit;
}

// A bit of an effort to reuse this code with dependency editing and the dependency wizard (editing multiple hosts at the same time)
$device_arr = array();
foreach ($_POST['device_ids'] as $dev) {
    if (!is_numeric($dev)) {
        echo 'Wrong device IDs!';
        exit;
    } else if ($dev == $_POST['parent_id']) {
        echo 'A device cannot depend itself';
        exit;
    }
    $device_arr[] = $dev;
}

$clause = dbGenPlaceholders(count($device_arr));

if (dbQuery('UPDATE `devices` set parent_id = '.$_POST['parent_id'].' WHERE `device_id` IN'.$clause, $device_arr)) {
    echo 'Host dependencies have been set';
    exit;
} else {
    echo 'ERROR: Host dependencies cannot be set.';
    exit;
}
