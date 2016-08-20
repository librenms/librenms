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
    die('ERROR: You need to be admin');
}

$ret = array();
$brk = false;
if (!is_numeric($_POST['map_id'])) {
    array_unshift($ret, 'ERROR: No map selected');
} else {
    if (dbFetchCell('SELECT COUNT(B.id) FROM alert_map,alert_map AS B WHERE alert_map.rule=B.rule && alert_map.id = ?', array($_POST['map_id'])) <= 1) {
        $rule              = dbFetchRow('SELECT alert_rules.id,alert_rules.device_id FROM alert_map,alert_rules WHERE alert_map.rule=alert_rules.id && alert_map.id = ?', array($_POST['map_id']));
        $rule['device_id'] = str_replace(':', '', $rule['device_id']);
        if (dbUpdate(array('device_id' => $rule['device_id']), 'alert_rules', 'id = ?', array($rule['id'])) >= 0) {
            $ret[] = 'Restored Rule: <i>'.$rule['id'].": device_id = '".$rule['device_id']."'</i>";
        } else {
            array_unshift($ret, 'ERROR: Rule '.$rule['id'].' has not been restored.');
            $brk = true;
        }
    }

    if ($brk === false && dbDelete('alert_map', '`id` =  ?', array($_POST['map_id']))) {
        $ret[] = 'Map has been deleted.';
    } else {
        array_unshift($ret, 'ERROR: Map has not been deleted.');
    }
}

foreach ($ret as $msg) {
    echo $msg.'<br/>';
}
