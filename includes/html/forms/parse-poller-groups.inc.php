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

if (! Auth::user()->hasGlobalAdmin()) {
    header('Content-type: text/plain');
    exit('ERROR: You need to be admin');
}

$group_id = ($_POST['group_id']);

if (is_numeric($group_id) && $group_id > 0) {
    $group = dbFetchRow('SELECT * FROM `poller_groups` WHERE `id` = ? LIMIT 1', [$group_id]);
    $output = [
        'group_name' => $group['group_name'],
        'descr'      => $group['descr'],
    ];
    header('Content-type: application/json');
    echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}
