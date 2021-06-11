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

if (! Auth::user()->hasGlobalAdmin()) {
    exit('ERROR: You need to be admin');
}

$ok = '';
$error = '';
$group_id = $_POST['group_id'];
$group_name = $_POST['group_name'];
$descr = $_POST['descr'];
if (! empty($group_name)) {
    if (is_numeric($group_id)) {
        if (dbUpdate(['group_name' => $group_name, 'descr' => $descr], 'poller_groups', 'id = ?', [$group_id]) >= 0) {
            $ok = 'Updated poller group';
        } else {
            $error = 'Failed to update the poller group';
        }
    } else {
        if (dbInsert(['group_name' => $group_name, 'descr' => $descr], 'poller_groups') >= 0) {
            $ok = 'Added new poller group';
        } else {
            $error = 'Failed to create new poller group';
        }
    }
} else {
    $error = "You haven't given your poller group a name, it feels sad :( - $group_name";
}

if (! empty($ok)) {
    exit("$ok");
} else {
    exit("ERROR: $error");
}
