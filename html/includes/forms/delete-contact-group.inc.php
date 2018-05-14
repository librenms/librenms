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

use LibreNMS\Authentication\Auth;

header('Content-type: application/json');

if (!Auth::user()->hasGlobalAdmin()) {
    die(json_encode([
        'status' => 'error',
        'message' => 'ERROR: You need to be admin.'
    ]));
}

$status = 'ok';
$message = '';

if (!is_numeric($_POST['group_id'])) {
    $status = 'error';
    $message = 'ERROR: No contact group selected';
} else {
    if (dbDelete('alert_contact_groups', '`contact_group_id` = ?', [$_POST['group_id']])) {
        dbDelete('contact_group_contact', '`contact_group_id`=?', [$_POST['group_id']]);
        dbDelete('alert_contact_map', '`contact_type`="group" AND `contact_or_group_id`=?', [$_POST['group_id']]);
        $message = 'Alert contact group has been deleted';
    } else {
        $message = 'ERROR: Alert contact group has not been deleted';
    }
}

die(json_encode([
    'status' => $status,
    'message'=> $message
]));
