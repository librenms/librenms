<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2017 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$oids = SnmpQuery::cache()->hideMib()->walk('CISCOSB-PHY-MIB::rlPhyTestGetResult')->table(1);

$multiplier = 1;
$divisor = 1000;
foreach ($oids as $index => $ciscosb_data) {
    foreach ($ciscosb_data as $key => $value) {
        if (isset($value['rlPhyTestTableTxOutput']) && is_numeric($value['rlPhyTestTableTxOutput']) && ($value['rlPhyTestTableRxOpticalPower'] != 0)) {
            $oid = '.1.3.6.1.4.1.9.6.1.101.90.1.2.1.3.' . $index . '.8';
            $port = PortCache::getByIfIndex(preg_replace('/^\d+\./', '', $index), $device['device_id']);
            $descr = trim($port?->ifDescr . ' Transmit Power');
            $dbm = $value['rlPhyTestTableTxOutput'] / $divisor;
            app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'dbm',
                'sensor_oid' => $oid,
                'sensor_index' => 'tx-' . $index,
                'sensor_type' => 'rlPhyTestTableTxOutput',
                'sensor_descr' => $descr,
                'sensor_divisor' => $divisor,
                'sensor_multiplier' => $multiplier,
                'sensor_limit_low' => null,
                'sensor_limit_low_warn' => null,
                'sensor_limit_warn' => null,
                'sensor_limit' => null,
                'sensor_current' => $dbm,
                'entPhysicalIndex' => $index,
                'entPhysicalIndex_measured' => 'ports',
                'user_func' => null,
                'group' => null,
            ]));
        }

        if (isset($value['rlPhyTestTableRxOpticalPower']) && is_numeric($value['rlPhyTestTableRxOpticalPower']) && ($value['rlPhyTestTableTxOutput'] != 0)) {
            $oid = '.1.3.6.1.4.1.9.6.1.101.90.1.2.1.3.' . $index . '.9';
            $port = PortCache::getByIfIndex(preg_replace('/^\d+\./', '', $index), $device['device_id']);
            $descr = trim($port?->ifDescr . ' Receive Power');
            $dbm = $value['rlPhyTestTableRxOpticalPower'] / $divisor;
            app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'dbm',
                'sensor_oid' => $oid,
                'sensor_index' => 'rx-' . $index,
                'sensor_type' => 'rlPhyTestTableRxOpticalPower',
                'sensor_descr' => $descr,
                'sensor_divisor' => $divisor,
                'sensor_multiplier' => $multiplier,
                'sensor_limit_low' => null,
                'sensor_limit_low_warn' => null,
                'sensor_limit_warn' => null,
                'sensor_limit' => null,
                'sensor_current' => $dbm,
                'entPhysicalIndex' => $index,
                'entPhysicalIndex_measured' => 'ports',
                'user_func' => null,
                'group' => null,
            ]));
        }
    }
}
