<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$cambium_type = $poll_device['sysDescr'];
$PMP = snmp_get($device, 'boxDeviceType.0', '-Oqv', 'WHISP-BOX-MIBV2-MIB');
$version = $cambium_type;

$filtered_words = array(
    'timing',
    'timeing'
);

$models = array(
    'BHUL450'   => 'PTP 450',
    'BHUL'      => 'PTP 230',
    'BH20'      => 'PTP 100',
    'CMM'       => 'CMM',
    'MIMO OFDM' => 'PMP 450',
    'OFDM'      => 'PMP 430',
    'AP'        => 'PMP 100'
);

foreach ($models as $desc => $model) {
    if (strstr($cambium_type, $desc)) {

        $hardware = $model;

        if (strstr($model, 'PTP')) {
            $masterSlaveMode = str_replace($filtered_words, "", snmp_get($device, 'bhTimingMode.0', '-Oqv', 'WHISP-BOX-MIBV2-MIB'));
            $hardware = $model . ' '. $masterSlaveMode;
            $version = snmp_get($device, 'boxDeviceTypeID.0', '-Oqv', 'WHISP-BOX-MIBV2-MIB');
        }

        if (strstr($model, 'PMP')) {
            if (strstr($version, "AP")) {
                $hardware = $model . ' AP';
            } elseif (strstr($version, "SM")) {
                $hardware = $model . ' SM';
            }
        }
    }
}
