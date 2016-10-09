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

if (is_admin() === false) {
    header('Content-type: text/plain');
    die('ERROR: You need to be admin');
}

$group_id = $_POST['group_id'];

if (is_numeric($group_id) && $group_id > 0) {
    $group       = dbFetchRow('SELECT * FROM `device_groups` WHERE `id` = ? LIMIT 1', array($group_id));
    $group_split = preg_split('/([a-zA-Z0-9_\-\.\=\%\<\>\ \"\'\!\~\(\)\*\/\@\[\]\^\$]+[&&\|\|]+)/', $group['pattern'], -1, (PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY));
    $count       = (count($group_split) - 1);
    if (preg_match('/\&\&$/', $group_split[$count]) == 1 || preg_match('/\|\|$/', $group_split[$count]) == 1) {
        $group_split[$count] = $group_split[$count];
    } else {
        $group_split[$count] = $group_split[$count].'  &&';
    }

    $output = array(
        'name'    => $group['name'],
        'desc'    => $group['desc'],
        'pattern' => $group_split,
    );
    header('Content-type: application/json');
    echo _json_encode($output);
}
