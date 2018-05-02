<?php

/*
 * LibreNMS
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
        'message' => 'ERROR: You need to be admin'
    ]));
}

$contact_id = $_POST['contact_id'];
// Retrieve alert contact
if (is_numeric($contact_id) && $contact_id > 0) {
    $contact = dbFetchRow('SELECT * FROM `alert_contacts` WHERE `contact_id` =? LIMIT 1', [$contact_id]);

    $details = [];
    $configs = dbFetchRows('SELECT `config_name` AS `name`, `config_value` AS `value` FROM `alert_configs` WHERE `config_type` = "contact" and `contact_or_transport_id`=?', [$contact_id]);
    // Get alert contact configuration details
    foreach ($configs as $detail) {
        $details[] = [
            'name' => $detail['name'],
            'value' => $detail['value']
        ];
    }
}

if (is_array($contact)) {
    die(json_encode([
        'name' => $contact['contact_name'],
        'type' => $contact['transport_type'],
        'config' => $contact['transport_config'],
        'details' => $details
    ]));
} else {
    die(json_encode([
        'status' => 'error',
        'message' => 'ERROR: No alert contact found'
    ]));
}


