<?php
/*
 * ciscosb.inc.php
 *
 * LibreNMS Cisco Small Business State Sensors
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @link        https://www.librenms.org
 *
 * @copyright   2017 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * @author      Søren Friis Rosiak <sorenrosiak@gmail.com>
 *
 * @copyright   2024 CTNET BV
 * @author      Rudy Broersma <r.broersma@ctnet.nl>

 */

use Illuminate\Support\Str;

$temp = SnmpQuery::hideMib()->walk('CISCOSB-rlInterfaces::swIfOperSuspendedStatus')->table(0);

$cur_oid = '.1.3.6.1.4.1.9.6.1.101.43.1.1.24.';

if (is_array($temp)) {
    //Create State Index
    $state_name = 'swIfOperSuspendedStatus';
    $states = [
        ['value' => 1, 'generic' => 2, 'graph' => 0, 'descr' => 'true'],
        ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'false'],
    ];
    create_state_index($state_name, $states);

    foreach ($temp[$state_name] as $index => $value) {
        $port_data = get_port_by_index_cache($device['device_id'], preg_replace('/^\d+\./', '', $index));

        $descr = trim(($port_data['ifDescr'] ?? '') . ' Suspended Status');
        if (Str::contains($descr, ['ethernet', 'Ethernet']) && $port_data['ifOperStatus'] !== 'notPresent') {
            //Discover Sensors
            discover_sensor(null, 'state', $device, $cur_oid . $index, $index, $state_name, $descr, 1, 1, null, null, null, null, $value, 'snmp', $index);

            //Create Sensor To State Index
            create_sensor_to_state_index($device, $state_name, $index);
        }
    }
}
