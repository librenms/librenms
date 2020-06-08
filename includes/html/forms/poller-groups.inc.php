<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

header('Content-type: text/plain');

if (!Auth::user()->hasGlobalAdmin()) {
    die('ERROR: You need to be admin');
}

$ok         = '';
$error      = '';
$group_id   = $_POST['group_id'];
$group_name = mres($_POST['group_name']);
$descr      = mres($_POST['descr']);
$rules      = mres($_POST['rules']);
if (!empty($group_name)) {

    $db_data = ['group_name' => $group_name,
                'descr'      => $descr,
                'rules'      => $rules];

    if (is_numeric($group_id)) {
        if (dbUpdate($db_data, 'poller_groups', 'id = ?', array($group_id)) >= 0) {
            $ok = 'Updated poller group';
        } else {
            $error = 'Failed to update the poller group';
        }
    } else {
        if (dbInsert($db_data, 'poller_groups') >= 0) {
            $ok = 'Added new poller group';
        } else {
            $error = 'Failed to create new poller group';
        }
    }
} else {
    $error = "You haven't given your poller group a name, it feels sad :( - $group_name";
}

if (!empty($ok)) {
    die("$ok");
} else {
    die("ERROR: $error");
}
