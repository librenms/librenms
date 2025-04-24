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
                    'sensor_class' => 'current',
                    'sensor_oid' => ".1.3.6.1.4.1.6321.1.2.2.2.1.6.2.1.6.$index",
                    'sensor_index' => $index,
                    'sensor_type' => 'exa',
                    'sensor_descr' => "$name xcvr bias",
                    'sensor_divisor' => 1000000,
                    'sensor_multiplier' => 1,
                    'sensor_limit_warn' => 80,
                    'sensor_limit' => 100,
                    'sensor_current' => $ponPort['E7-Calix-MIB::e7OltPonPortTxBias'] / 1000000,
                    'entPhysicalIndex' => $ifIndex,
                    'entPhysicalIndex_measured' => 'port',
                    'group' => 'transceiver',
                ]));
            }
        }
    }
}
