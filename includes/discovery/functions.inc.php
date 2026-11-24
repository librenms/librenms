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

use LibreNMS\Enum\SensorCategory;
use LibreNMS\Enum\SensorType;
use LibreNMS\Util\Number;

// Discover sensors
function discover_sensor($unused, SensorType|string $class, $device, $oid, $index, $type, $descr, $divisor = 1, $multiplier = 1, $low_limit = null, $low_warn_limit = null, $warn_limit = null, $high_limit = null, $current = null, $poller_type = 'snmp', $entPhysicalIndex = null, $entPhysicalIndex_measured = null, $user_func = null, $group = null, $rrd_type = 'GAUGE', ?SensorCategory $category = null): bool
{
    // Temporary: convert back to string until Sensor::sensor_class is cast to SensorType
    if ($class instanceof SensorType) {
        $class = $class->value;
    }

    $low_limit = set_null($low_limit);
    $low_warn_limit = set_null($low_warn_limit);
    $warn_limit = set_null($warn_limit);
    $high_limit = set_null($high_limit);
    $current = Number::cast($current);

    if (! is_numeric($divisor)) {
        $divisor = 1;
    }

    app('sensor-discovery')->discover(new \App\Models\Sensor([
        'poller_type' => $poller_type,
        'sensor_class' => $class,
        'device_id' => $device['device_id'],
        'sensor_oid' => $oid,
        'sensor_index' => $index,
        'sensor_type' => $type,
        'sensor_descr' => $descr,
        'sensor_divisor' => $divisor,
        'sensor_multiplier' => $multiplier,
        'sensor_limit' => $high_limit,
        'sensor_limit_warn' => $warn_limit,
        'sensor_limit_low' => $low_limit,
        'sensor_limit_low_warn' => $low_warn_limit,
        'sensor_current' => $current,
        'entPhysicalIndex' => $entPhysicalIndex,
        'entPhysicalIndex_measured' => $entPhysicalIndex_measured,
        'user_func' => $user_func,
        'group' => $group,
        'category' => $category,
        'rrd_type' => $rrd_type,
    ]));

    return true;
}
