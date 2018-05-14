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

$group_id = $_POST['group_id'];
// Retrieve alert contact
if (is_numeric($group_id) && $group_id > 0) {
    $name = dbFetchCell('SELECT `contact_group_name` FROM `alert_contact_groups` WHERE `contact_group_id`=? LIMIT 1', [$group_id]);
    
    $query = "SELECT `a`.`contact_id`, `transport_type`, `contact_name` FROM `contact_group_contact` AS `a` LEFT JOIN `alert_contacts` AS `b` ON `a`.`contact_id`=`b`.`contact_id` WHERE `contact_group_id`=?";
    
    $members = [];
    foreach (dbFetchRows($query, [$group_id]) as $member) {
        $members[] =  [
            'id' => $member['contact_id'],
            'text' => ucfirst($member['transport_type']).": ".$member['contact_name']
        ];
    }
}

if (is_array($members)) {
    die(json_encode([
        'name' => $name,
        'members' => $members
    ]));
} else {
    die(json_encode([
        'status' => 'error',
        'message' => 'ERROR: No alert contact found'
    ]));
}
