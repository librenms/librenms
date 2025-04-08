<?php
/*
 * asa.inc.php
 *
 * LibreNMS state sensor discovery module for Cisco ASA appliances
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @link        https://www.librenms.org
 *
 * @copyright   2016 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * @author      Søren Friis Rosiak <sorenrosiak@gmail.com>
 * @copyright   2024 CTNET BV
 * @author      Rudy Broersma <r.broersma@ctnet.nl>
 */

$cur_oid = '.1.3.6.1.4.1.9.9.147.1.2.1.1.1.3.';
$temp = SnmpQuery::cache()->walk('CISCO-FIREWALL-MIB::cfwHardwareStatusTable')->table(1);

if (is_array($temp)) {
    //Create State Index
    if (strstr($temp['netInterface']['CISCO-FIREWALL-MIB::cfwHardwareStatusDetail'], 'not Configured') == false) {
        $state_name = 'cfwHardwareStatus';
        $states = [
            ['value' => 1, 'generic' => 2, 'graph' => 0, 'descr' => 'other'],
            ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'up'],
            ['value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'down'],
            ['value' => 4, 'generic' => 2, 'graph' => 0, 'descr' => 'error'],
            ['value' => 5, 'generic' => 2, 'graph' => 0, 'descr' => 'overTemp'],
            ['value' => 6, 'generic' => 2, 'graph' => 0, 'descr' => 'busy'],
            ['value' => 7, 'generic' => 2, 'graph' => 0, 'descr' => 'noMedia'],
            ['value' => 8, 'generic' => 2, 'graph' => 0, 'descr' => 'backup'],
            ['value' => 9, 'generic' => 0, 'graph' => 0, 'descr' => 'active'],
            ['value' => 10, 'generic' => 0, 'graph' => 0, 'descr' => 'standby'],
        ];
        create_state_index($state_name, $states);

        foreach ($temp as $index => $entry) {
            $descr = ucwords(trim(preg_replace('/\s*\([^\s)]*\)/', '', $temp[$index]['CISCO-FIREWALL-MIB::cfwHardwareInformation'])));

            if ($index == 'netInterface') {
                $oid_index = 4;
            } elseif ($index == 'primaryUnit') {
                $oid_index = 6;
            } elseif ($index == 'secondaryUnit') {
                $oid_index = 7;
            }

            $sensor_value = $temp[$index]['CISCO-FIREWALL-MIB::cfwHardwareStatusValue'];

            //Discover Sensors
            discover_sensor(null, 'state', $device, $cur_oid . $oid_index, $oid_index, $state_name, $descr, 1, 1, null, null, null, null, $sensor_value, 'snmp', $oid_index);
        }
    }
}
