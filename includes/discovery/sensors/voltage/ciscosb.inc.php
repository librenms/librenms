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
$divisor = 1000000;

foreach ($oids as $index => $ciscosb_data) {
    foreach ($ciscosb_data as $key => $value) {
        if (! isset($value['rlPhyTestTableTransceiverSupply'])) {
            continue;
        }

        $oid = '.1.3.6.1.4.1.9.6.1.101.90.1.2.1.3.' . $index . '.6';
        $port = PortCache::getByIfIndex(preg_replace('/^\d+\./', '', $index), $device['device_id']);
        $descr = trim($port?->ifDescr . ' Supply Voltage');
        $voltage = $value['rlPhyTestTableTransceiverSupply'] / $divisor;

        if (is_numeric($voltage) && ($value['rlPhyTestTableTransceiverTemp'] != 0)) {
            app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'voltage',
                'sensor_oid' => $oid,
                'sensor_index' => $index,
                'sensor_type' => 'rlPhyTestTableTransceiverSupply',
                'sensor_descr' => $descr,
                'sensor_divisor' => $divisor,
                'sensor_multiplier' => $multiplier,
                'sensor_limit_low' => null,
                'sensor_limit_low_warn' => null,
                'sensor_limit_warn' => null,
                'sensor_limit' => null,
                'sensor_current' => $voltage,
                'entPhysicalIndex' => $index,
                'entPhysicalIndex_measured' => 'ports',
                'user_func' => null,
                'group' => null,
            ]));
        }
    }
}
