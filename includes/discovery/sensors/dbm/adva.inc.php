<?php
/**
 * LibreNMS - ADVA device support - Pre-Cache for Sensors
 *
 * @category   Network_Monitoring
 * @package    LibreNMS
 * @subpackage ADVA device support
 * @author     Christoph Zilian <czilian@hotmail.com>
 * @license    http://gnu.org/copyleft/gpl.html GNU GPL
 * @link       https://github.com/librenms/librenms/

 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 **/

//********* ADVA FSP3000 R7 Series


if (starts_with($device['sysObjectID'], 'enterprises.2544.1.11.1.1')) {
    $multiplier = 1;
    $divisor = 10;

    foreach ($pre_cache['fsp3kr7'] as $index => $entry) {
// other AidStrings to be inclued.
        if ($entry['entityFacilityAidString'] and $entry['pmSnapshotCurrentInputPower']) {
            $oidRX = '.1.3.6.1.4.1.2544.1.11.7.7.2.3.1.2.' . $index;
            $limit_low                 = -20;
            $warn_limit_low            = -18;
            $limit                     = 7;
            $warn_limit                = 5;
            $currentRX                   = $entry['pmSnapshotCurrentInputPower'];
            $descr                       = $entry['entityFacilityAidString'].' RX';

            discover_sensor(
                $valid['sensor'],
                'dbm',
                $device,
                $oidRX,
                $descr,
                'advafsp3kr7',
                $descr,
                $divisor,
                $multiplier,
                $limit_low,
                $warn_limit_low,
                $warn_limit,
                $limit,
                $currentRX
            );
        }//End if Input Power

        if ($entry['entityFacilityAidString'] and $entry['pmSnapshotCurrentOutputPower']) {
            $oidTX = '.1.3.6.1.4.1.2544.1.11.7.7.2.3.1.1.' . $index;
            $limit_low                 = -20;
            $warn_limit_low            = -18;
            $limit                     = 7;
            $warn_limit                = 5;
            $currentTX                   = $entry['pmSnapshotCurrentOutputPower'];
            $descr                       = $entry['entityFacilityAidString'].' TX';

            discover_sensor(
                $valid['sensor'],
                'dbm',
                $device,
                $oidTX,
                $descr,
                'advafsp3kr7',
                $descr,
                $divisor,
                $multiplier,
                $limit_low,
                $warn_limit_low,
                $warn_limit,
                $limit,
                $currentTX
            );
        }//End if Output Power
    }//End foreach entry
}//End IF Equipment Model
unset($entry);

//********* End of ADVA FSP3000 R7 Series
