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

if (!is_numeric($vars['alert_id'])) {
    echo 'ERROR: No alert selected';
    exit;
} else {
    if (dbDelete('alert_rules', '`id` =  ?', array($vars['alert_id']))) {
        dbDelete('alert_device_map', 'rule_id=?', [$vars['alert_id']]);
        dbDelete('alert_group_map', 'rule_id=?', [$vars['alert_id']]);
        dbDelete('alert_transport_map', 'rule_id=?', [$vars['alert_id']]);
        dbDelete('alert_template_map', 'alert_rule_id=?', [$vars['alert_id']]);
        echo 'Alert rule has been deleted.';
        exit;
    } else {
        echo 'ERROR: Alert rule has not been deleted.';
        exit;
    }
}
