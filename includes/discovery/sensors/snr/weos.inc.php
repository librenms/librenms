<?php
/*
 * LibreNMS
 *
 * weos.inc.php
 *
 * LibreNMS snr discovery module module for Westermo WeOS
 *
 * This program is free software: you can redistribute it and/or modify
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

foreach ($pre_cache['weos_hdsl2ShdslEndpointCurrTable'] as $index => $data) {
    if (is_numeric($data['hdsl2ShdslEndpointCurrSnrMgn'])) {
        // $index is 4096.0.0.1 but ifName is only 4096. Use explode to break the string into an array.
        $portExplode = explode('.', $index)[0];
        $descr = "Port {$pre_cache['weos_ifName'][$portExplode]['ifName']} SNR";
        $oid = '.1.3.6.1.2.1.10.48.1.5.1.2.' . $index;
        $value = $data['hdsl2ShdslEndpointCurrSnrMgn'];
        discover_sensor($valid['sensor'], 'snr', $device, $oid, 'hdsl2ShdslEndpointCurrSnrMgn.' . $index, 'weos', $descr, $divisor, $multiplier, 8, 13, 98, 99, $value);
        var_dump($descr);
        var_dump($index);
        var_dump($portExplode);
    }
}
