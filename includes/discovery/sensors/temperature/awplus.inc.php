<?php

$divisor = 1000;

if ($pre_cache['awplus-sfpddm']) {
    foreach ($pre_cache['awplus-sfpddm'] as $index => $data) {
        if (isset($data['atPluggableDiagTempStatusReading'])) {
            $ifIndex = explode('.', $index, 2)[0];
            $high_limit = isset($data['atPluggableDiagTempAlarmMax']) ? $data['atPluggableDiagTempAlarmMax'] / $divisor : null;
            $high_warn_limit = isset($data['atPluggableDiagTempWarningMax']) ? $data['atPluggableDiagTempWarningMax'] / $divisor : null;
            $low_warn_limit = isset($data['atPluggableDiagTempWarningMin']) ? $data['atPluggableDiagTempWarningMin'] / $divisor : null;
            $low_limit = isset($data['atPluggableDiagTempAlarmMin']) ? $data['atPluggableDiagTempAlarmMin'] / $divisor : null;

            $tmp = get_port_by_index_cache($device['device_id'], $ifIndex);
            $descr = $tmp['ifName'];
            $oid = '.1.3.6.1.4.1.207.8.4.4.3.28.1.1.1.3.' . $index;
            discover_sensor(
                $valid['sensor'],
                'temperature',
                $device,
                $oid,
                'SFP:' . $descr,
                'atPluggableDiagTempStatusReading',
                'SFP:' . $descr,
                $divisor,
                null, // $multiplier,
                $low_limit,
                $low_warn_limit,
                $high_warn_limit,
                $high_limit,
                $value,
                'snmp',
                $ifIndex,
                null,
                null,
                'SFP Temperature'
            );
        }
    }
}
