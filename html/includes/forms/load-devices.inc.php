<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2017 Aldemir Akpinar <https://github.com/aldemira/>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if (is_admin() || is_read()) {
    $query = "SELECT `d`.`device_id` as `device_id`, `d`.`hostname` as `hostname` FROM `devices` AS `d` WHERE `disabled` = 0 GROUP BY `d`.`device_id`, `d`.`hostname`";
} else {
    $query = "SELECT `d`.`device_id` as `device_id`, `d`.`hostname` as `hostname` FROM `devices` AS `d` LEFT JOIN `devices_perms` AS `dp` ON `d`.`device_id`=`dp`.`device_id` WHERE `disabled` = 0 GROUP BY `d`.`device_id`, `d`.`hostname`";
}

$devices = dbFetchRows($query);
$status = array(
    'devices' => $devices,
);
header('Content-Type: application/json');
echo _json_encode($status);
