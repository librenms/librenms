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

$rule   = mres($_POST['rule']);
$target = mres($_POST['target']);
$map_id = mres($_POST['map_id']);
$ret    = array();

if (empty($rule) || empty($target)) {
    $ret[] = 'ERROR: No map was generated';
} else {
    $raw  = $rule;
    $rule = dbFetchCell('SELECT id FROM alert_rules WHERE name = ?', array($rule));
    if (!is_numeric($rule)) {
        array_unshift($ret, "ERROR: Could not find rule for '".$raw."'");
    } else {
        $raw = $target;
        if ($target[0].$target[1] == 'g:') {
            $target = 'g'.dbFetchCell('SELECT id FROM device_groups WHERE name = ?', array(substr($target, 2)));
        } else {
            $target = dbFetchCell('SELECT device_id FROM devices WHERE hostname = ?', array($target));
        }

        if (!is_numeric(str_replace('g', '', $target))) {
            array_unshift($ret, "ERROR: Could not find entry for '".$raw."'");
        } else {
            if (is_numeric($map_id) && $map_id > 0) {
                if (dbUpdate(array('rule' => $rule, 'target' => $target), 'alert_map', 'id=?', array($map_id)) >= 0) {
                    $ret[] = 'Edited Map: <i>'.$map_id.': '.$rule.' = '.$target.'</i>';
                } else {
                    array_unshift($ret, 'ERROR: Failed to edit Map: <i>'.$map_id.': '.$rule.' = '.$target.'</i>');
                }
            } else {
                if (dbInsert(array('rule' => $rule, 'target' => $target), 'alert_map')) {
                    $ret[] = 'Added Map: <i>'.$rule.' = '.$target.'</i>';
                } else {
                    array_unshift($ret, 'ERROR: Failed to add Map: <i>'.$rule.' = '.$target.'</i>');
                }
            }

            if (($tmp = dbFetchCell('SELECT device_id FROM alert_rules WHERE id = ?', array($rule))) && $tmp[0] != ':') {
                if (dbUpdate(array('device_id' => ':'.$tmp), 'alert_rules', 'id=?', array($rule)) >= 0) {
                    $ret[] = 'Edited Rule: <i>'.$rule." device_id = ':".$tmp."'</i>";
                } else {
                    array_unshift($ret, 'ERROR: Failed to edit Rule: <i>'.$rule.": device_id = ':".$tmp."'</i>");
                }
            }
        }//end if
    }//end if
}//end if
foreach ($ret as $msg) {
    echo $msg.'<br/>';
}
