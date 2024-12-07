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
                    'sensor_class' => 'state',
                    'sensor_oid' => ".1.3.6.1.4.1.6321.1.2.2.2.1.6.2.1.4.$index",
                    'sensor_index' => 'ponStatus.' . $index,
                    'sensor_type' => 'E7-Calix-MIB::e7OltPonPortStatus',
                    'sensor_descr' => "$name Status",
                    'sensor_divisor' => 1,
                    'sensor_multiplier' => 1,
                    'sensor_current' => $ponPort['E7-Calix-MIB::e7OltPonPortStatus'],
                    'entPhysicalIndex' => $ifIndex,
                    'entPhysicalIndex_measured' => 'port',
                    'group' => $name,
                ]));
                // FIXME
//                    ->withStateTranslations('E7-Calix-MIB::e7OltPonPortStatus', [
//                    ['value' => 0, 'generic' => 3, 'graph' => 1, 'descr' => 'invalid'],
//                    ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'linkUp'],
//                    ['value' => 2, 'generic' => 2, 'graph' => 1, 'descr' => 'linkDown'],
//                ]);
            }
        }
    }
}
