<?php

use App\Models\Sensor;
use LibreNMS\OS\Cdata;

// ONU registration history from NSCRTV-PON-TREE-EXT-MIB onuRegInfoTable; a rising dereg count means a flapping ONU
$onuNames = $os->onuNames();
$columns = [
    Cdata::ONU_REG_TIMES => 'Registrations',
    Cdata::ONU_DEREG_TIMES => 'Deregistrations',
];

foreach ($os->onuRegInfo() as $index => $row) {
    foreach ($columns as $column => $label) {
        if (! is_numeric($row[$column] ?? null)) {
            continue;
        }

        app('sensor-discovery')->discover(new Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'count',
            'sensor_oid' => Cdata::ONU_REG_TABLE . ".$column.$index",
            'sensor_index' => "$column.$index",
            'sensor_type' => 'cdata',
            'sensor_descr' => ($onuNames[$index] ?? "onu $index") . " $label",
            'sensor_divisor' => 1,
            'sensor_multiplier' => 1,
            'sensor_current' => $row[$column],
            'group' => 'ONU',
        ]));
    }
}

unset($onuNames, $columns, $index, $row, $column, $label);
