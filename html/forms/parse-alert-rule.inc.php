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

if(is_admin() === false) {
    die('ERROR: You need to be admin');
}

$alert_id = $_POST['alert_id'];

if(is_numeric($alert_id) && $alert_id > 0) {
    $rule = dbFetchRow("SELECT * FROM `alert_rules` WHERE `id` = ? LIMIT 1",array($alert_id));
    $rule_split = preg_split('/([a-zA-Z0-9_\-\.\=\%\<\>\ \"\'\!\~\(\)\*\/]+[&&\|\|]+)/',$rule['rule'], -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    $count = count($rule_split) - 1;
    $rule_split[$count] = $rule_split[$count].'  &&';
    $output = array('severity'=>$rule['severity'],'extra'=>$rule['extra'],'name'=>$rule['name'],'rules'=>$rule_split);
    echo _json_encode($output);
}
