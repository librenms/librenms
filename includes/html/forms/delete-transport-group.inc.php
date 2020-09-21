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

header('Content-type: application/json');

if (! Auth::user()->hasGlobalAdmin()) {
    exit(json_encode([
        'status' => 'error',
        'message' => 'ERROR: You need to be admin.',
    ]));
}

$status = 'ok';
$message = '';

if (! is_numeric($vars['group_id'])) {
    $status = 'error';
    $message = 'ERROR: No transport group selected';
} else {
    if (dbDelete('alert_transport_groups', '`transport_group_id` = ?', [$vars['group_id']])) {
        dbDelete('transport_group_transport', '`transport_group_id`=?', [$vars['group_id']]);
        dbDelete('alert_transport_map', '`target_type`="group" AND `transport_or_group_id`=?', [$vars['group_id']]);
        $message = 'Alert transport group has been deleted';
    } else {
        $message = 'ERROR: Alert transport group has not been deleted';
    }
}

exit(json_encode([
    'status' => $status,
    'message'=> $message,
]));
