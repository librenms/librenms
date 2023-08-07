<?php
/**
 * LibreNMS - ADVA device support - Pre-Cache for Sensors
 *
 * @category   Network_Monitoring
 *
 * @author     Christoph Zilian <czilian@hotmail.com> && Khairi Azmi <mkhairi47@hotmail.com>
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

//********* ADVA FSP3000 R7 Series

$multiplier = 1;
$divisor = 10;

foreach ($pre_cache['adva_fsp3kr7'] as $index => $entry) {
    if (($entry['entityOpticalMuxAidString'] || $entry['entityFacilityAidString']) &&
        ($entry['pmSnapshotCurrentInputPower'] || $entry['pmSnapshotCurrentOutputPower'])) {
        if ($entry['entityOpticalMuxAidString']) {
            $oidRX = '.1.3.6.1.4.1.2544.1.11.7.7.2.3.1.2.' . $index;
            $descr = $entry['entityOpticalMuxAidString'] . ' RX';
        } else {
            $oidRX = '.1.3.6.1.4.1.2544.1.11.7.7.2.3.1.2.' . $index;
            $descr = $entry['entityFacilityAidString'] . ' RX';
        }

        if ($entry['pmSnapshotCurrentInputPower']) {
            $currentRX = $entry['pmSnapshotCurrentInputPower'] / $divisor;

            discover_sensor(
                $valid['sensor'],
                'dbm',
                $device,
                $oidRX,
                'pmSnapshotCurrentInputPower' . $index,
                'adva_fsp3kr7',
                $descr,
                $divisor,
                $multiplier,
                null,
                null,
                null,
                null,
                $currentRX,
                'snmp',
                $entPhysicalIndex,
                $entPhysicalIndex_measured,
                null,
                'Laser RX'
            );
        }

        if ($entry['pmSnapshotCurrentOutputPower']) {
            $oidTX = '.1.3.6.1.4.1.2544.1.11.7.7.2.3.1.1.' . $index;

            if ($entry['entityOpticalMuxAidString']) {
                $descr = $entry['entityOpticalMuxAidString'] . ' TX';
            } else {
                $descr = $entry['entityFacilityAidString'] . ' TX';
            }

            $currentTX = $entry['pmSnapshotCurrentOutputPower'] / $divisor;

            discover_sensor(
                $valid['sensor'],
                'dbm',
                $device,
                $oidTX,
                'pmSnapshotCurrentOutputPower' . $index,
                'adva_fsp3kr7',
                $descr,
                $divisor,
                $multiplier,
                null,
                null,
                null,
                null,
                $currentTX,
                'snmp',
                $entPhysicalIndex,
                $entPhysicalIndex_measured,
                null,
                'Laser TX'
            );
        }
    }
}
