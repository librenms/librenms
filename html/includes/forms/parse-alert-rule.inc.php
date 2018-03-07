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

$alert_id = $_POST['alert_id'];
$template_id = $_POST['template_id'];

if (is_numeric($alert_id) && $alert_id > 0) {
    $rule               = dbFetchRow('SELECT * FROM `alert_rules` WHERE `id` = ? LIMIT 1', array($alert_id));
} elseif (is_numeric($template_id) && $template_id >= 0) {
    $tmp_rules = get_rules_from_json();
    $rule = $tmp_rules[$template_id];
}
if (is_array($rule)) {
    if (empty($rule['query_builder'])) {
        $sql_query = $rule['rule'];
        $sql_query = str_replace('&&', 'AND', $sql_query);
        $sql_query = str_replace('||', 'OR', $sql_query);
        $sql_query = str_replace('%', '', $sql_query);
        $sql_query = str_replace('"', "'", $sql_query);
        $sql_query = str_replace('~', "REGEXP", $sql_query);
        $rule['query_builder'] = $sql_query;
    }
    $output             = array(
        'severity'      => $rule['severity'],
        'extra'         => $rule['extra'],
        'name'          => $rule['name'],
        'proc'          => $rule['proc'],
        'query_builder' => $rule['query_builder'],
    );
    header('Content-type: application/json');
    echo _json_encode($output);
}
