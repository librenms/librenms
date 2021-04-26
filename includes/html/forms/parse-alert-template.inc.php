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

if (! Auth::user()->hasGlobalAdmin()) {
    header('Content-type: text/plain');
    exit('ERROR: You need to be admin');
}

$template_id = $vars['template_id'];
$template_edit = is_numeric($template_id) && $template_id > 0;

$rules = [];
$output = [
    'template'  => '',
    'name'      => '',
    'title'     => '',
    'title_rec' => '',
    'type'      => '',
    'rules'     => $rules,
];

if ($template_edit) {
    $template = dbFetchRow('SELECT * FROM `alert_templates` WHERE `id` = ? LIMIT 1', [$template_id]);
    $output = [
        'template'  => $template['template'],
        'name'      => $template['name'],
        'title'     => $template['title'],
        'title_rec' => $template['title_rec'],
        'type'      => $template['type'],
    ];
}

foreach (dbFetchRows('SELECT `id`,`rule`,`name` FROM `alert_rules` order by `name`', []) as $rule) {
    $is_selected = $template_edit ? dbFetchCell('SELECT `alert_templates_id` FROM `alert_template_map` WHERE `alert_rule_id` = ? AND `alert_templates_id` = ?', [$rule['id'], $template_id]) : null;
    $is_available = dbFetchCell('SELECT `alert_templates_id` FROM `alert_template_map` WHERE `alert_rule_id` = ?', [$rule['id']]);
    $rules[] = [
        'id' => $rule['id'],
        'name' => $rule['name'],
        'selected' => isset($is_selected),
        'used' => isset($is_available) ? dbFetchCell('SELECT `name` FROM `alert_templates` WHERE `id` = ?', [$is_available]) : '',
    ];
}
$output['rules'] = $rules;

header('Content-type: application/json');
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
