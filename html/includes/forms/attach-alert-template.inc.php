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

header('Content-type: text/plain');

if (!Auth::user()->hasGlobalAdmin()) {
    die('ERROR: You need to be admin');
}

if (!is_numeric($_POST['template_id'])) {
    echo 'ERROR: No template selected';
    exit;
} else {
    $rules   = preg_split('/,/', mres($_POST['rule_id']));
    $ids = [];
    foreach ($rules as $rule_id) {
        $db_id = dbInsert(array('alert_rule_id' => $rule_id, 'alert_templates_id' => mres($_POST['template_id'])), 'alert_template_map');
        if ($db_id > 0) {
            $ids[]   = $db_id;
        } else {
            echo 'ERROR: Alert rules have not been attached to this template.';
            exit;
        }
    }

    if (!empty($ids)) {
        dbDelete('alert_template_map', 'id NOT IN ' . dbGenPlaceholders(count($ids)) . ' AND alert_templates_id =?', array_merge([$_POST['template_id']], $ids));
        echo "Alert rules have been attached to this template.";
        exit;
    }
}//end if
