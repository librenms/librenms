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

// TODO: In the future when contact groups are added, need to grab the contact_group_id 
// associated with this contact and check to see if it is the last member in the 
// contact group and if so, delete the entry in the contact group table

if (!is_numeric($_POST['contact_id'])) {
    $status = 'error';
    $message = 'ERROR: No contact selected';
} else {
    if (dbDelete('alert_contacts', '`contact_id` = ?', array($_POST['contact_id']))) {
        dbDelete('alert_configs', '`config_type` = "contact" and `contact_or_transport_id` = ?', [$_POST['contact_id']]);
        dbDelete('alert_contact_map', '`contact_type = "contact" and `contact_or_group_id` = ?', [$_POST['contact_id']]);
    
        $message = 'Alert contact has been deleted';
    } else {
        $message = 'ERROR: Alert contact has not been deleted';
    }
}

die(json_encode([
    'status' => $status,
    'message'=> $message
]));
