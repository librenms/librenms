<?php

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\OS;
use SnmpQuery;

class GudeEpc extends OS
{
    public function discoverOS(Device $device): void
    {
        $models = [
            ['mib' => 'GUDEADS-EPC1202-MIB', 'prefix' => 'epc1202'],
            ['mib' => 'GUDEADS-EPC1104-MIB', 'prefix' => 'epc1104'],
            ['mib' => 'GUDEADS-EPC1105-MIB', 'prefix' => 'epc1105'],
            ['mib' => 'GUDEADS-EPC1121-MIB', 'prefix' => 'epc1121'],
            ['mib' => 'GUDEADS-EPC1141-MIB', 'prefix' => 'epc1141'],
            ['mib' => 'GUDEADS-EPC8001-MIB', 'prefix' => 'epc8001'],
            ['mib' => 'GUDEADS-EPC8021-MIB', 'prefix' => 'epc8021'],
            ['mib' => 'GUDEADS-EPC8031-MIB', 'prefix' => 'epc8031'],
            ['mib' => 'GUDEADS-EPC8035-MIB', 'prefix' => 'epc8035'],
            ['mib' => 'GUDEADS-EPC8041-MIB', 'prefix' => 'epc8041'],
            ['mib' => 'GUDEADS-EPC8045-MIB', 'prefix' => 'epc8045'],
            ['mib' => 'GUDEADS-EPC8221-MIB', 'prefix' => 'epc8221'],
            ['mib' => 'GUDEADS-EPC8226-MIB', 'prefix' => 'epc8226'],
            ['mib' => 'GUDEADS-EPC8291-MIB', 'prefix' => 'epc8291'],
            ['mib' => 'GUDEADS-EPC8314-MIB', 'prefix' => 'epc8314'],
            ['mib' => 'GUDEADS-EPC8316-MIB', 'prefix' => 'epc8316'],
            ['mib' => 'GUDEADS-EPC873300-MIB', 'prefix' => 'epc873300'],
            ['mib' => 'GUDEADS-EPC871210-MIB', 'prefix' => 'epc871210'],
        ];

        $hardwareOids = [];
        $versionOids = [];
        $serialOids = [];
        foreach ($models as $model) {
            $oid = $model['mib'] . '::' . $model['prefix'];
            $hardwareOids[] = $oid . 'ProdName.0';
            $versionOids[] = $oid . 'FWVersion.0';
            $serialOids[] = $oid . 'SerialNumber.0';
        }

        $oids = array_merge($hardwareOids, $versionOids, $serialOids);
        $response = SnmpQuery::get($oids);

        $device->hardware = $response->value($hardwareOids) ?: null;
        $device->version = $response->value($versionOids) ?: null;
        $device->serial = $response->value($serialOids) ?: null;
    }
}
