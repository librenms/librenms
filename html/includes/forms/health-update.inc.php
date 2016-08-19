<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
header('Content-type: text/plain');

// FUA

if (is_admin() === false) {
    die('ERROR: You need to be admin');
}

if (!is_numeric($_POST['device_id']) || !is_numeric($_POST['sensor_id']) || (empty($_POST['data']) || !isset($_POST['data']))) {
    echo 'error with data';
    exit;
} else {
    $update = dbUpdate(array($_POST['value_type'] => $_POST['data'], 'sensor_custom' => 'Yes'), 'sensors', '`sensor_id` = ? AND `device_id` = ?', array($_POST['sensor_id'], $_POST['device_id']));
    if (!empty($update) || $update == '0') {
        echo 'success';
        exit;
    } else {
        echo 'error';
        exit;
    }
}
