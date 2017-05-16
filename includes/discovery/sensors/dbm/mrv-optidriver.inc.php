<?php

/*
 * LibreNMS Interface Power dBm module for the MRV® OptiDriver® Optical Transport Platform
 *
 * © 2017 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$hardware = snmp_get($device, 'nbsCmmcChassisModel.1', '-Ovqs', 'NBS-CMMC-MIB', $config['install_dir'].'/mibs/mrv');
$version = snmp_get($device, 'nbsCmmcSysFwVers.0', '-Ovqs', 'NBS-CMMC-MIB', $config['install_dir'].'/mibs/mrv');
$serial = snmp_get($device, 'nbsCmmcChassisSerialNum.1', '-Ovqs', 'NBS-CMMC-MIB', $config['install_dir'].'/mibs/mrv');
$features       = '';


echo 'MRV OptiDriver ';

$multiplier = 1;
$divisor = 1000;
$limit_low = -100;
$warn_limit_low = -100;
$limit = 100;
$warn_limit = 100;

foreach ($pre_cache['mrv-optidriver_oids'] as $index => $entry) {
//    if (is_numeric(str_replace('dBm', '', $entry['nbsCmmcPortRxPower']))) {
        $oid = '.1.3.6.1.4.1.629.200.8.1.1.32.' . $index;
        $descr = dbFetchCell('SELECT `ifDescr` FROM `ports` WHERE `ifName`= ? AND `device_id` = ?', array($index, $device['device_id'])) . ' Rx Power';
        $currentrx = $entry['nbsCmmcPortRxPower'];
        discover_sensor(
            $valid['sensor'],
            'dbm',
            $device,
            $oid,
            'rx-' . $index,
            'mrv-optidriver',
            $descr,
            $divisor,
            $multiplier,
            $limit_low,
            $warn_limit_low,
            $warn_limit,
            $limit,
            $currentrx,
            'snmp'
        );
//    }
}

foreach ($pre_cache['mrv-optidriver_oids'] as $index => $entry) {
//    if (is_numeric(str_replace('dBm', '', $entry['nbsCmmcPortTxPower']))) {
        $oid = '.1.3.6.1.4.1.629.200.8.1.1.31.' . $index;
        $descr = dbFetchCell('SELECT `ifDescr` FROM `ports` WHERE `ifName`= ? AND `device_id` = ?', array($index, $device['device_id'])) . ' Tx Power';
        $currenttx = $entry['nbsCmmcPortTxPower'];
        discover_sensor(
            $valid['sensor'],
            'dbm',
            $device,
            $oid,
            'tx-' . $index,
            'mrv-optidriver',
            $descr,
            $divisor,
            $multiplier,
            $limit_low,
            $warn_limit_low,
            $warn_limit,
            $limit,
            $currenttx,
            'snmp'
        );
//    }
}
