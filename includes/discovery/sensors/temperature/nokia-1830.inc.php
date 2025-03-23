<?php
/**
 * LibreNMS - Nokia PSD SFP DDM Sensors
 *
 * @category   Network_Monitoring
 *
 * @author     Nick Peelman <nick@peelman.us>
 * @license    https://gnu.org/copyleft/gpl.html GNU GPL
 *
 * @link       https://github.com/librenms/librenms/
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 **/

// *************************************************************
// ***** Temperature Sensors for Nokia PSD
// *************************************************************
$multiplier = 1;
$divisor = 10;

if (is_array($pre_cache['ddmvalues']) && is_array($pre_cache['iftable'])) {
    d_echo('Nokia PSD DDM Temperature Sensors\n');
    foreach (array_keys($pre_cache['iftable']) as $index) {
        $ddmIndex = "$index.2";
        if ($pre_cache['iftable'][$index]['ifAdminStatus'] == 'up' && $pre_cache['ddmvalues'][$ddmIndex]) {
            $oid = '.1.3.6.1.4.1.7483.2.2.7.3.1.4.1.2.' . $ddmIndex;
            $descr = $pre_cache['ifnames'][$index]['ifName'];
            $current = $pre_cache['ddmvalues'][$ddmIndex]['tnPsdDdmDataValue'] / $divisor;
            discover_sensor(
                null,
                'temperature',
                $device,
                $oid,
                $ddmIndex,
                'nokia-1830',
                $descr,
                $divisor,
                $multiplier,
                null,
                null,
                null,
                null,
                $current,
                'snmp',
                null,
                null,
                null,
                'Transceivers'
            );
        }
    }
} //  ************** End of Sensors for Nokia PSD **********
