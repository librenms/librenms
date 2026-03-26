<?php

use App\Models\Sensor;
use App\Models\StateTranslation;
use LibreNMS\Enum\Severity;

$cur_oid = '.1.3.6.1.3.94.1.8.1.6.';

// These sensors are not provided as tables. They are strings of the form:
//    Sensor Name: Value
//
// The list is also a mix of voltages, temperatures, and state, and uses both F and C for temperatures
// The order is not stable between software versions

$oids = SnmpQuery::cache()->hideMib()->numericIndex()->walk('FCMGMT-MIB::connUnitSensorMessage')->valuesByIndex();

if (is_array($oids)) {
    foreach ($oids as $index => $entry) {
        if (str_contains((string) $entry['connUnitSensorMessage'], 'Status')) {
            $connUnitSensorMessage = explode(':', (string) $entry['connUnitSensorMessage']);
            $value = array_pop($connUnitSensorMessage) === ' OK' ? 1 : 2;
            $descr = implode(':', $connUnitSensorMessage);

            app('sensor-discovery')->discover(new Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'state',
                'sensor_oid' => $cur_oid . $index,
                'sensor_index' => $index,
                'sensor_type' => 'dellme',
                'sensor_descr' => $descr,
                'sensor_divisor' => 1,
                'sensor_multiplier' => 1,
                'sensor_current' => $value,
            ]))->withStateTranslations('dellme', [
                StateTranslation::define('OK', 1, Severity::Ok),
                StateTranslation::define('Not OK', 2, Severity::Error),
            ]);
        }
    }
}
unset($cur_oid,
    $connUnitSensorMessage,
    $value,
    $descr,
    $oids
);
