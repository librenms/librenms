<?php

$divisor = 10000;

if ($pre_cache['awplus-sfpddm']) {
    foreach ($pre_cache['awplus-sfpddm'] as $index => $data) {
        if (isset($data['atPluggableDiagVccStatusReading'])) {
            $ifIndex = explode('.', $index, 2)[0];
            $high_limit = isset($data['atPluggableDiagVccAlarmMax']) ? ($data['atPluggableDiagVccAlarmMax'] / $divisor) : null;
            $high_warn_limit = isset($data['atPluggableDiagVccWarningMax']) ? ($data['atPluggableDiagVccWarningMax'] / $divisor) : null;
            $low_warn_limit = isset($data['atPluggableDiagVccWarningMin']) ? ($data['atPluggableDiagVccWarningMin'] / $divisor) : null;
            $low_limit = isset($data['atPluggableDiagVccAlarmMin']) ? ($data['atPluggableDiagVccAlarmMin'] / $divisor) : null;

            $tmp = get_port_by_index_cache($device['device_id'], $ifIndex);
            $descr = $tmp['ifName'];
            $oid = '.1.3.6.1.4.1.207.8.4.4.3.28.1.2.1.3.' . $index;
            discover_sensor(
                $valid['sensor'],
                'voltage',
                $device,
                $oid,
                'SFP:' . $descr,
                'atPluggableDiagVccStatusReading',
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
                'SFP'
            );
        }
    }
}
