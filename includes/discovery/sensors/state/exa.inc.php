<?php

use App\Models\Sensor;
use App\Models\StateTranslation;
use LibreNMS\Enum\Severity;
use LibreNMS\OS\Exa;

$ponTable = SnmpQuery::cache()->walk('E7-Calix-MIB::e7OltPonPortTable')->table(3);

foreach ($ponTable as $e7OltPonPortShelf => $ponShelf) {
    foreach ($ponShelf as $e7OltPonPortSlot => $ponSlot) {
        foreach ($ponSlot as $e7OltPonPortId => $ponPort) {
            if ($ponPort['E7-Calix-MIB::e7OltPonPortStatus'] != 0) {
                $ifIndex = Exa::getIfIndex($e7OltPonPortShelf, $e7OltPonPortSlot, $e7OltPonPortId, 'gpon'); // we know these are GPON, so we can infer the ifIndex
                $index = "$e7OltPonPortShelf.$e7OltPonPortSlot.$e7OltPonPortId";
                $name = "$e7OltPonPortShelf/$e7OltPonPortSlot/$e7OltPonPortId";

                app('sensor-discovery')->discover(new Sensor([
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
                ]))->withStateTranslations('E7-Calix-MIB::e7OltPonPortStatus', [
                    StateTranslation::define('invalid', 0, Severity::Unknown),
                    StateTranslation::define('linkUp', 1, Severity::Ok),
                    StateTranslation::define('linkDown', 2, Severity::Error),
                ]);
            }
        }
    }
}
