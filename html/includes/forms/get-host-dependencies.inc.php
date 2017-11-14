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
    if (isset($_POST['viewtype'])) {
        if ($_POST['viewtype'] == 'fulllist') {
            $deps_query = "SELECT a.device_id as id, a.hostname as hostname, b.hostname as parent, b.device_id as parentid from devices as a LEFT JOIN devices as b ON a.parent_id = b.device_id ORDER BY a.hostname";
            $device_deps = dbFetchRows($deps_query);
            $status = array('status' => 0, 'deps' => $device_depts);
        } else {
            $device_deps = dbFetchRows('SELECT `device_id`,`hostname` from `devices` WHERE `parent_id` = ? ORDER BY `hostname` ASC', array($_POST['parent_id']));
            if ($_POST['viewtype'] == 'fromparent' && is_numeric($_POST['parent_id'])) {
                if ($_POST['parent_id'] == 0) {
                    $device_deps = dbFetchRows('SELECT `device_id`,`hostname` from `devices` WHERE `parent_id` = 0 OR `parent_id` is null ORDER BY `hostname` ASC');
                } else {
                    $device_deps = dbFetchRows(
                        'SELECT `device_id`,`hostname` from `devices` WHERE `parent_id` = ? ORDER BY `hostname` ASC',
                        array($_POST['parent_id'])
                    );
                }
                $status = array('status' => 0, 'deps' => $device_deps);
            }
        }
    } else {
        if (!is_numeric($_POST['device_id'])) {
            $status = array('status' => 1, 'message' => 'Wrong device id!');
        } else {
            $device_deps = dbFetchRows(
                'SELECT `device_id`,`hostname`,`parent_id` from `devices` WHERE `device_id` <>  ? ORDER BY `hostname` ASC',
                array($_POST['device_id'])
            );
            $status = array('status' => 0, 'deps' => $device_deps);
        }
     }
 }
 
header('Content-Type: application/json');
echo _json_encode($status);
