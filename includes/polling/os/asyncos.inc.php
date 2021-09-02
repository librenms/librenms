<?php
/*
 * LibreNMS Cisco AsyncOS information module
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2017 Mike Williams
 * @author     Mike Williams <mike@mgww.net>
 */

use LibreNMS\RRD\RrdDefinition;

[$hardware,$version,,$serial] = explode(',', $device['sysDescr']);

preg_match('/\w[\d]+\w?/', $hardware, $regexp_results);
$hardware = $regexp_results[0];

preg_match('/[\d\.-]+/', $version, $regexp_results);
$version = $regexp_results[0];

preg_match('/[[\w]+-[\w]+/', $serial, $regexp_results);
$serial = $regexp_results[0];

// Get stats only if device is web proxy
if (strcmp($device['sysObjectID'], '.1.3.6.1.4.1.15497.1.2') == 0) {
    $connections = snmp_get($device, 'tcpCurrEstab.0', '-OQv', 'TCP-MIB');

    if (is_numeric($connections)) {
        $rrd_name = 'asyncos_conns';
        $rrd_def = RrdDefinition::make()->addDataset('connections', 'GAUGE', 0, 50000);

        $fields = [
            'connections' => $connections,
        ];

        $tags = compact('rrd_def');
        data_update($device, 'asyncos_conns', $tags, $fields);

        $os->enableGraph('asyncos_conns');
    }
}
