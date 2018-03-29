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

if (is_admin() === false) {
    die('ERROR: You need to be admin');
}

if (!is_numeric($_POST['template_id'])) {
    echo 'ERROR: No template selected';
    exit;
} else {
    $rules   = preg_split('/,/', mres($_POST['rule_id']));
    $success = false;
    foreach ($rules as $rule_id) {
        $db_id = dbInsert(array('alert_rule_id' => $rule_id, 'alert_templates_id' => mres($_POST['template_id'])), 'alert_template_map');
        if ($db_id > 0) {
            $success = true;
            $ids[]   = $db_id;
        } else {
            echo 'ERROR: Alert rules have not been attached to this template.';
            exit;
        }
    }

    if ($success === true) {
        dbDelete('alert_template_map', 'id NOT IN ('.implode(',', $ids).') AND alert_templates_id =?', array($_POST['template_id']));
        echo "Alert rules have been attached to this template. $template_map_ids";
        exit;
    }
}//end if
