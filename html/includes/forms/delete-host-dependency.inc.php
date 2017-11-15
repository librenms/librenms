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

if (is_admin() === false) {
    $status = array('status' => 1, 'message' => 'You need to be admin');
} else {
    if (!is_numeric($_POST['device_id'])) {
        $status = array('status' => 1, 'message' => 'Wrong device id!');
    } else {
        if (dbUpdate(array('parent_id' => 0), 'devices', '`device_id` =  ?', array($_POST['device_id']))) {
            $status = array('status' => 0, 'message' => 'Host dependency has been deleted.');
        } else {
            $status = array('status' => 1, 'message' => 'Host Dependency cannot be deleted.');
        }
    }
}

header('Content-Type: application/json');
echo _json_encode($status);
