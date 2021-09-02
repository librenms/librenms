<?php
/*
 * LibreNMS Network Management and Monitoring System
 * Copyright (C) 2006-2011, Observium Developers - http://www.observium.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See COPYING for more details.
 */

use LibreNMS\Config;

$query = 'SELECT `sensor_class` FROM `sensors` WHERE `device_id` = ?';
$params = [$device['device_id']];

$submodules = Config::get('poller_submodules.sensors', []);
if (! empty($submodules)) {
    $query .= ' AND `sensor_class` IN ' . dbGenPlaceholders(count($submodules));
    $params = array_merge($params, $submodules);
}

$query .= ' GROUP BY `sensor_class`';

foreach (dbFetchRows($query, $params) as $sensor_type) {
    poll_sensor($device, $sensor_type['sensor_class']);
}

unset($submodules, $sensor_type, $query, $params);
