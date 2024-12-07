<?php

$ponTable = SnmpQuery::cache()->walk('E7-Calix-MIB::e7OltPonPortTable')->table(3);

foreach ($ponTable as $e7OltPonPortShelf => $ponShelf) {
    foreach ($ponShelf as $e7OltPonPortSlot => $ponSlot) {
        foreach ($ponSlot as $e7OltPonPortId => $ponPort) {
            if ($ponPort['E7-Calix-MIB::e7OltPonPortStatus'] != 0) {
                $ifIndex = \LibreNMS\OS\Exa::getIfIndex($e7OltPonPortShelf, $e7OltPonPortSlot, $e7OltPonPortId, 'gpon'); // we know these are GPON, so we can infer the ifIndex
                $index = "$e7OltPonPortShelf.$e7OltPonPortSlot.$e7OltPonPortId";
                $name = "$e7OltPonPortShelf/$e7OltPonPortSlot/$e7OltPonPortId";

                app('sensor-discovery')->discover(new \App\Models\Sensor([
                    'poller_type' => 'snmp',
                    'sensor_class' => 'dbm',
                    'sensor_oid' => ".1.3.6.1.4.1.6321.1.2.2.2.1.6.2.1.7.$index",
                    'sensor_index' => 'tx.' . $index,
                    'sensor_type' => 'exa',
                    'sensor_descr' => "$name xcvr TX power",
                    'sensor_divisor' => 1,
                    'sensor_multiplier' => 1,
                    'sensor_limit_low' => 0,
                    'sensor_limit_low_warn' => 0.2,
                    'sensor_limit_warn' => 5.8,
                    'sensor_limit' => 6,
                    'sensor_current' => mw_to_dbm($ponPort['E7-Calix-MIB::e7OltPonPortTxPower']),
                    'entPhysicalIndex' => $ifIndex,
                    'entPhysicalIndex_measured' => 'port',
                    'group' => 'transceiver',
                    'user_func' => 'mw_to_dbm',
                ]));

                app('sensor-discovery')->discover(new \App\Models\Sensor([
                    'poller_type' => 'snmp',
                    'sensor_class' => 'dbm',
                    'sensor_oid' => ".1.3.6.1.4.1.6321.1.2.2.2.1.6.2.1.8.$index",
                    'sensor_index' => 'rx.' . $index,
                    'sensor_type' => 'exa',
                    'sensor_descr' => "$name xcvr RX power",
                    'sensor_divisor' => 1,
                    'sensor_multiplier' => 1,
                    'sensor_limit_low' => -28,
                    'sensor_limit_low_warn' => -27,
                    'sensor_limit_warn' => -8,
                    'sensor_limit' => -7,
                    'sensor_current' => mw_to_dbm($ponPort['E7-Calix-MIB::e7OltPonPortRxPower']),
                    'entPhysicalIndex' => $ifIndex,
                    'entPhysicalIndex_measured' => 'port',
                    'group' => 'transceiver',
                    'user_func' => 'mw_to_dbm',
                ]));
            }
        }
    }
}
