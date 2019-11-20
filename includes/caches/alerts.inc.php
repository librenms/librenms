<?php

if (Auth::user()->hasGlobalRead()) {
    $data['active_count'] = array('query' => 'SELECT COUNT(`alerts`.`id`)  FROM `alerts` LEFT JOIN `devices` ON `alerts`.`device_id`=`devices`.`device_id`  RIGHT JOIN `alert_rules` ON `alerts`.`rule_id`=`alert_rules`.`id` WHERE 1 AND `alerts`.`state` NOT IN (0,2) AND `devices`.`disabled` = 0 AND `devices`.`ignore` = 0');
} else {
    $data['active_count'] = array(
        'query'  => 'SELECT COUNT(`alerts`.`id`)  FROM `alerts` LEFT JOIN `devices` ON `alerts`.`device_id`=`devices`.`device_id` LEFT JOIN `devices_perms` AS `DP` ON `devices`.`device_id` = `DP`.`device_id`  RIGHT JOIN `alert_rules` ON `alerts`.`rule_id`=`alert_rules`.`id` WHERE 1 AND `alerts`.`state` NOT IN (0,2)  AND `devices`.`disabled` = 0 AND `devices`.`ignore` = 0 AND `DP`.`user_id`=?',
        'params' => array(Auth::id()),
    );
}
