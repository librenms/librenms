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

use LibreNMS\Authentication\LegacyAuth;

header('Content-type: application/json');

if (!LegacyAuth::user()->hasGlobalAdmin()) {
    $response = array(
        'status'  => 'error',
        'message' => 'Need to be admin',
    );
    echo _json_encode($response);
    exit;
}

$status = 'error';
$message = 'Ops. Something happened. Enable debug and check librenms.log';
set_debug(true);
if (!is_numeric($_POST['template_id'])) {
    $message = 'ERROR: No template selected';
} else {
    $rules   = preg_split('/,/', $_POST['rule_id']);
    $ids = [];
    foreach (dbFetchRows('SELECT `alert_rule_id` FROM `alert_template_map` WHERE `alert_templates_id` = ?', array($_POST['template_id'])) as $rule) {
        $old_rules[] = $rule['alert_rule_id'];
    }
    foreach ($rules as $rule_id) {
        $db_id = dbInsert(array('alert_rule_id' => $rule_id, 'alert_templates_id' =>$_POST['template_id']), 'alert_template_map');
        if ($db_id > 0) {
            $ids[]   = $db_id;
            $new_rules[] = $rule_id;
        } else {
            $status = 'error';
            $message = 'Alert rules have not been attached to this template.';
        }
    }

    if (!empty($ids)) {
        dbDelete('alert_template_map', 'id NOT IN ' . dbGenPlaceholders(count($ids)) . ' AND alert_templates_id =?', array_merge($ids, [$_POST['template_id']]));
        $status = 'ok';
        $message = "Alert rules have been attached to this template.";
    }
}//end if
//$old_rules = array_diff($old_rules, $new_rules);
foreach ($old_rules as $rule) {
    $rule_name[] = dbFetchCell("SELECT `name` FROM `alert_rules` WHERE `id` = ". $rule);
}
foreach ($new_rules as $template) {
    $template_name[] = dbFetchCell("SELECT `name` FROM `alert_templates` WHERE `id` = ". $_POST['template_id']);
    $nrule_name[] = dbFetchCell("SELECT `name` FROM `alert_rules` WHERE `id` = ". $template);
}
$response = array(
    'status'        => $status,
    'message'       => $message,
    'new_rules'     => $new_rules,
    'nrule_name'     => $nrule_name,
    'old_rules'     => $old_rules,
    'rule_name'     => $rule_name,
    'template_name'     => $template_name
);
echo _json_encode($response);
