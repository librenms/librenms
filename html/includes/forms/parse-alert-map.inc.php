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
    header('Content-type: text/plain');
    die('ERROR: You need to be admin');
}

$map_id = $_POST['map_id'];

if (is_numeric($map_id) && $map_id > 0) {
    $map = dbFetchRow('SELECT alert_rules.name,alert_map.target FROM alert_map,alert_rules WHERE alert_map.rule=alert_rules.id && alert_map.id = ?', array($map_id));
    if ($map['target'][0] == 'g') {
        $map['target'] = 'g:'.dbFetchCell('SELECT name FROM device_groups WHERE id = ?', array(substr($map['target'], 1)));
    } else {
        $map['target'] = dbFetchCell('SELECT hostname FROM devices WHERE device_id = ?', array($map['target']));
    }

    $output = array(
               'rule'   => $map['name'],
               'target' => $map['target'],
              );
    header('Content-type: application/json');
    echo _json_encode($output);
}
