<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2016 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

$oids = SnmpQuery::cache()->hideMib()->walk('CISCOSB-POE-MIB::rlPethPsePortTable')->table(2);

$divisor = '1000';
foreach ($oids as $unit => $unitData) {
    foreach ($unitData as $ifIndex => $data) {
        if (isset($data['rlPethPsePortOutputPower'])) {
            $value = $data['rlPethPsePortOutputPower'] / $divisor;
            if ($value) {
                $port = PortCache::getByIfIndex($ifIndex, $device['device_id']);
                $descr = $port?->ifDescr . ' PoE';
                $index = $unit . '.' . $ifIndex;
                $oid = '.1.3.6.1.4.1.9.6.1.101.108.1.1.5.' . $index;

                app('sensor-discovery')->discover(new \App\Models\Sensor([
                    'poller_type' => 'snmp',
                    'sensor_class' => 'power',
                    'sensor_oid' => $oid,
                    'sensor_index' => $index,
                    'sensor_type' => 'ciscosb',
                    'sensor_descr' => $descr,
                    'sensor_divisor' => $divisor,
                    'sensor_multiplier' => 1,
                    'sensor_limit_low' => null,
                    'sensor_limit_low_warn' => null,
                    'sensor_limit_warn' => null,
                    'sensor_limit' => isset($data['rlPethPsePortOperPowerLimit']) ? ($data['rlPethPsePortOperPowerLimit'] / $divisor) : null,
                    'sensor_current' => $value,
                    'entPhysicalIndex' => $index,
                    'entPhysicalIndex_measured' => null,
                    'user_func' => null,
                    'group' => null,
                ]));
            }
        }
    }
}
