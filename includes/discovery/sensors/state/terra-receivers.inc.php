<?php

use App\Models\Sensor;
use App\Models\StateTranslation;
use LibreNMS\Enum\Severity;
use LibreNMS\Util\Oid;

if (str_contains((string) $device['sysDescr'], 'sdi480')) {
    $data = SnmpQuery::cache()->walk('TERRA-sdi480-MIB::sdi480alarms')->values();
    if (is_array($data)) {
        for ($i = 1; $i <= 8; $i++) {
            $value = $data['TERRA-sdi480-MIB::alarmUnlock' . $i . '.0'] ?? 1;
            $oid = Oid::of('TERRA-sdi480-MIB::alarmUnlock' . $i . '.0')->toNumeric();
            app('sensor-discovery')->discover(new Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'state',
                'sensor_oid' => $oid,
                'sensor_index' => $i,
                'sensor_type' => 'terra-receivers',
                'sensor_descr' => 'Input ' . $i,
                'sensor_divisor' => 1,
                'sensor_multiplier' => 1,
                'sensor_current' => $value,
                'group' => 'Tuner Lock',
            ]))->withStateTranslations('terra-receivers', [
                StateTranslation::define('OK', 0, Severity::Ok),
                StateTranslation::define('Error', 1, Severity::Error),
            ]);
        }
    }
}
unset($data,
    $value,
    $oid
);
