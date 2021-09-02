<?php

if (Auth::user()->hasGlobalRead()) {
    $data['active_count'] = ['query' => 'SELECT COUNT(`alerts`.`id`)  FROM `alerts` LEFT JOIN `devices` ON `alerts`.`device_id`=`devices`.`device_id`  RIGHT JOIN `alert_rules` ON `alerts`.`rule_id`=`alert_rules`.`id` WHERE 1 AND `alerts`.`state` NOT IN (0,2) AND `devices`.`disabled` = 0 AND `devices`.`ignore` = 0'];
} else {
    $device_ids = Permissions::devicesForUser()->toArray() ?: [0];
    $perms_sql = '`D`.`device_id` IN ' . dbGenPlaceholders(count($device_ids));

    $data['active_count'] = [
        'query'  => 'SELECT COUNT(`alerts`.`id`)  FROM `alerts` LEFT JOIN `devices` ON `alerts`.`device_id`=`devices`.`device_id` RIGHT JOIN `alert_rules` ON `alerts`.`rule_id`=`alert_rules`.`id` WHERE $perms_sql AND `alerts`.`state` NOT IN (0,2)  AND `devices`.`disabled` = 0 AND `devices`.`ignore` = 0',
        'params' => $device_ids,
    ];
}
