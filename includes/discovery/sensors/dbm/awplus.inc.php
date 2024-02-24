<?php

$divisor = 10000;

if ($pre_cache['awplus-sfpddm']) {
    foreach ($pre_cache['awplus-sfpddm'] as $index => $data) {
        if (isset($data['atPluggableDiagTxPowerStatusReading'])) {
            $ifIndex = explode('.', $index, 2)[0];
            $high_limit = isset($data['atPluggableDiagTxPowerAlarmMax']) ? mw_to_dbm($data['atPluggableDiagTxPowerAlarmMax'] / $divisor) : null;
            $high_warn_limit = isset($data['atPluggableDiagTxPowerWarningMax']) ? mw_to_dbm($data['atPluggableDiagTxPowerWarningMax'] / $divisor) : null;
            $low_warn_limit = isset($data['atPluggableDiagTxPowerWarningMin']) ? mw_to_dbm($data['atPluggableDiagTxPowerWarningMin'] / $divisor) : null;
            $low_limit = isset($data['atPluggableDiagTxPowerAlarmMin']) ? mw_to_dbm($data['atPluggableDiagTxPowerAlarmMin'] / $divisor) : null;

            $tmp = get_port_by_index_cache($device['device_id'], $ifIndex);
            $descr = $tmp['ifName'];
            $oid = '.1.3.6.1.4.1.207.8.4.4.3.28.1.4.1.3.' . $index;
            discover_sensor(
                $valid['sensor'],
                'dbm',
                $device,
                $oid,
                'SFP:' . $descr,
                'atPluggableDiagTxPowerStatusReading',
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
                'mw_to_dbm',
                'TX Power'
            );
        }
    }
}
if ($pre_cache['awplus-sfpddm']) {
    foreach ($pre_cache['awplus-sfpddm'] as $index => $data) {
        if (isset($data['atPluggableDiagRxPowerStatusReading'])) {
            $ifIndex = explode('.', $index, 2)[0];
            $high_limit = isset($data['atPluggableDiagRxPowerAlarmMax']) ? mw_to_dbm($data['atPluggableDiagRxPowerAlarmMax'] / $divisor) : null;
            $high_warn_limit = isset($data['atPluggableDiagRxPowerWarningMax']) ? mw_to_dbm($data['atPluggableDiagRxPowerWarningMax'] / $divisor) : null;
            $low_warn_limit = isset($data['atPluggableDiagRxPowerWarningMin']) ? mw_to_dbm($data['atPluggableDiagRxPowerWarningMin'] / $divisor) : null;
            $low_limit = isset($data['atPluggableDiagRxPowerAlarmMin']) ? mw_to_dbm($data['atPluggableDiagRxPowerAlarmMin'] / $divisor) : null;

            $tmp = get_port_by_index_cache($device['device_id'], $ifIndex);
            $descr = $tmp['ifName'];
            $oid = '.1.3.6.1.4.1.207.8.4.4.3.28.1.5.1.3.' . $index;
            discover_sensor(
                $valid['sensor'],
                'dbm',
                $device,
                $oid,
                'SFP:' . $descr,
                'atPluggableDiagRxPowerStatusReading',
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
                'mw_to_dbm',
                'RX Power'
            );
        }
    }
}
