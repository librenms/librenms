<?php

/*
 * Sensor State discovery module for the CradlePoint WiPipe Platform
 *
 * © 2017 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo 'CradlePoint WiPipe';

foreach ($pre_cache['wipipe_oids'] as $index => $entry) {
    // Modem Connection Status
    if ($entry['mdmStatus']) {
        $cur_oid = '.1.3.6.1.4.1.20992.1.2.2.1.5.';
        //Create State Index
        $state_name = 'mdmStatus';
        $states = [
            ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'established'],
            ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'establishing'],
            ['value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'ready'],
            ['value' => 4, 'generic' => 2, 'graph' => 0, 'descr' => 'error'],
            ['value' => 5, 'generic' => 2, 'graph' => 0, 'descr' => 'disconnected'],
            ['value' => 6, 'generic' => 1, 'graph' => 0, 'descr' => 'disconnecting'],
            ['value' => 7, 'generic' => 2, 'graph' => 0, 'descr' => 'suspended'],
            ['value' => 8, 'generic' => 3, 'graph' => 0, 'descr' => 'empty'],
            ['value' => 9, 'generic' => 3, 'graph' => 0, 'descr' => 'notconfigured'],
            ['value' => 10, 'generic' => 1, 'graph' => 0, 'descr' => 'userstopped'],
        ];
        create_state_index($state_name, $states);

        // Get Modem Model & Phone Number for description
        $modemdesc = $entry['mdmDescr'];
        $modemmdn = $entry['mdmMDN'];
        $descr = 'mdmStatus - ' . $modemdesc . ' - ' . $modemmdn;
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $cur_oid . $index, $index, $state_name, $descr, 1, 1, null, null, null, null, $entry['mdmStatus'], 'snmp', $index);
        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
}
// Device Firmware Upgrade Status
$upgradestatus = snmpwalk_cache_oid($device, 'devFWUpgradeStatus', [], 'WIPIPE-MIB');
foreach ($upgradestatus as $index => $entry) {
    $cur_oid = '.1.3.6.1.4.1.20992.1.1.4.';
    //Create State Index
    $state_name = 'devFWUpgradeStatus';
    $states = [
        ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'idle'],
        ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'upgrading'],
        ['value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'uptodate'],
        ['value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'updateAvail'],
        ['value' => 5, 'generic' => 2, 'graph' => 0, 'descr' => 'failure'],
    ];
    create_state_index($state_name, $states);

    $descr = 'Firmware Upgrade Status';
    //Discover Sensors
    discover_sensor($valid['sensor'], 'state', $device, $cur_oid . $index, $index, $state_name, $descr, 1, 1, null, null, null, null, $entry['devFWUpgradeStatus'], 'snmp', $index);
    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $index);
}
