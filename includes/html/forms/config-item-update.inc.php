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

if (!Auth::user()->hasGlobalAdmin()) {
    die('ERROR: You need to be admin');
}

if (!is_numeric($_POST['config_id']) || empty($_POST['data'])) {
    echo 'error with data';
    exit;
} else {
    $data   = mres($_POST['data']);
    $update = dbUpdate(array('config_value' => "$data"), 'config', '`config_id` = ?', array($_POST['config_id']));
    if (!empty($update) || $update == '0') {
        echo 'success';
        exit;
    } else {
        echo 'error';
        exit;
    }
}
