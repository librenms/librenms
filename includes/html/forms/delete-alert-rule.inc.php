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

if (! is_numeric($vars['alert_id'])) {
    echo 'ERROR: No alert selected';
    exit;
} else {
    $alert_name = dbFetchCell('SELECT name FROM alert_rules WHERE id=?', [$vars['alert_id']]);
    $alert_msg_prefix = 'Alert rule';
    if ($alert_name) {
        $alert_msg_prefix .= ' ' . $alert_name;
    }
    if (! $alert_name) {
        $alert_msg_prefix .= ' id ' . $vars['alert_id'];
    }
    if (dbDelete('alert_rules', '`id` =  ?', [$vars['alert_id']])) {
        dbDelete('alert_device_map', 'rule_id=?', [$vars['alert_id']]);
        dbDelete('alert_group_map', 'rule_id=?', [$vars['alert_id']]);
        dbDelete('alert_location_map', 'rule_id=?', [$vars['alert_id']]);
        dbDelete('alert_transport_map', 'rule_id=?', [$vars['alert_id']]);
        dbDelete('alert_template_map', 'alert_rule_id=?', [$vars['alert_id']]);
        echo $alert_msg_prefix . ' has been deleted.';
        exit;
    } else {
        echo 'ERROR: ' . $alert_msg_prefix . ' has not been deleted.';
        exit;
    }
}
