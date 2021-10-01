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

use LibreNMS\Alert\AlertDB;

if (! Auth::user()->hasGlobalAdmin()) {
    exit('ERROR: You need to be admin');
}

$rule = implode(' ', $_POST['rules']);
$rule = rtrim($rule, '&|');
$query = AlertDB::genSQL($rule);
$alert_id = $_POST['alert_id'];
$count = $_POST['count'];
$delay = $_POST['delay'];
$interval = $_POST['interval'];
$mute = $_POST['mute'];
$invert = $_POST['invert'];
$name = $_POST['name'];
if ($_POST['proc'] != '') {
    $proc = $_POST['proc'];
} else {
    $proc = '';
}

if (empty($rule)) {
    $update_message = 'ERROR: No rule was generated - did you forget to click and / or?';
} elseif (validate_device_id($_POST['device_id']) || $_POST['device_id'] == '-1' || $_POST['device_id'][0] == ':') {
    $device_id = $_POST['device_id'];
    if (! is_numeric($count)) {
        $count = '-1';
    }

    $delay_sec = convert_delay($delay);
    $interval_sec = convert_delay($interval);
    if ($mute == 'on') {
        $mute = true;
    } else {
        $mute = false;
    }

    if ($invert == 'on') {
        $invert = true;
    } else {
        $invert = false;
    }

    $extra = [
        'mute'     => $mute,
        'count'    => $count,
        'delay'    => $delay_sec,
        'invert'   => $invert,
        'interval' => $interval_sec,
    ];
    $extra_json = json_encode($extra);
    if (is_numeric($alert_id) && $alert_id > 0) {
        if (dbUpdate(['rule' => $rule, 'severity' => $_POST['severity'], 'extra' => $extra_json, 'name' => $name, 'proc' => $proc, 'query' => $query], 'alert_rules', 'id=?', [$alert_id]) >= 0) {
            $update_message = "Edited Rule: <i>$name: $rule</i>";
        } else {
            $update_message = 'ERROR: Failed to edit Rule: <i>' . $rule . '</i>';
        }
    } else {
        if (is_array($_POST['maps'])) {
            $device_id = ':' . $device_id;
        }
        if (dbInsert(['device_id' => $device_id, 'rule' => $rule, 'severity' => $_POST['severity'], 'extra' => $extra_json, 'disabled' => 0, 'name' => $name, 'proc' => $proc, 'query' => $query], 'alert_rules')) {
            $update_message = "Added Rule: <i>$name: $rule</i>";
            if (is_array($_POST['maps'])) {
                foreach ($_POST['maps'] as $target) {
                    $_POST['rule'] = $name;
                    $_POST['target'] = $target;
                    $_POST['map_id'] = '';
                    include 'create-map-item.inc.php';
                    unset($ret, $target, $raw, $rule, $msg, $map_id);
                }
            }
        } else {
            $update_message = 'ERROR: Failed to add Rule: <i>' . $rule . '</i>';
        }
    }//end if
} else {
    $update_message = 'ERROR: invalid device ID or not a global alert';
}//end if
echo $update_message;
