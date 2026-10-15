<?php

use App\Models\Sensor;
use LibreNMS\OS\Cdata;

// time since the ONU last registered, seconds on the wire, LibreNMS runtime sensors are minutes
$onuNames = $os->onuNames();

foreach ($os->onuRegInfo() as $index => $row) {
    $value = $row[Cdata::ONU_ONLINE_DURATION] ?? null;
    if (! is_numeric($value)) {
        continue;
    }

    app('sensor-discovery')->discover(new Sensor([
        'poller_type' => 'snmp',
        'sensor_class' => 'runtime',
        'sensor_oid' => Cdata::ONU_REG_TABLE . '.' . Cdata::ONU_ONLINE_DURATION . ".$index",
        'sensor_index' => $index,
        'sensor_type' => 'cdata',
        'sensor_descr' => ($onuNames[$index] ?? "onu $index") . ' Online',
        'sensor_divisor' => 60,
        'sensor_multiplier' => 1,
        'sensor_current' => $value / 60,
        'group' => 'ONU',
    ]));
}

unset($onuNames, $index, $row, $value);
