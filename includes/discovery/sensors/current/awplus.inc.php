<?php

$divisor = 1000000;

if ($pre_cache['awplus-sfpddm']) {
    foreach ($pre_cache['awplus-sfpddm'] as $index => $data) {
        if (isset($data['atPluggableDiagTxBiasStatusReading'])) {
            $ifIndex = explode('.', $index, 2)[0];
            $high_limit = isset($data['atPluggableDiagTxBiasAlarmMax']) ? ($data['atPluggableDiagTxBiasAlarmMax'] / $divisor) : null;
            $high_warn_limit = isset($data['atPluggableDiagTxBiasWarningMax']) ? ($data['atPluggableDiagTxBiasWarningMax'] / $divisor) : null;
            $low_warn_limit = isset($data['atPluggableDiagTxBiasWarningMin']) ? ($data['atPluggableDiagTxBiasWarningMin'] / $divisor) : null;
            $low_limit = isset($data['atPluggableDiagTxBiasAlarmMin']) ? ($data['atPluggableDiagTxBiasAlarmMin'] / $divisor) : null;

            $tmp = get_port_by_index_cache($device['device_id'], $ifIndex);
            $descr = $tmp['ifName'];
            $oid = '.1.3.6.1.4.1.207.8.4.4.3.28.1.3.1.3.' . $index;
            discover_sensor(
                $valid['sensor'],
                'current',
                $device,
                $oid,
                'SFP:' . $descr,
                'atPluggableDiagTxBiasStatusReading',
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
