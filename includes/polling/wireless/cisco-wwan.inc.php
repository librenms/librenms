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

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/cisco-wwan-rssi.rrd";
$rssi = snmp_get($device, "1.3.6.1.4.1.9.9.661.1.3.4.1.1.1.13", "-Ovqn", "");

if (is_numeric($rssi)) {
    if (!is_file($rrd_filename)) {
        rrdtool_create($rrd_filename, " --step 300 DS:rssi:GAUGE:600:-150:5000".$config['rrd_rra']);
    }
    $fields = array(
        'rssi' => $rssi,
    );
    rrdtool_update($rrd_filename, $fields);
    $graphs['cisco_wwan_rssi'] = TRUE;
    unset($rrd_filename,$rssi);
}


$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/cisco-wwan-mnc.rrd";
$mnc = snmp_get($device, "1.3.6.1.4.1.9.9.661.1.3.2.1.11.13", "-Ovqn", "");
if (is_numeric($mnc)) {
    if (!is_file($rrd_filename)) {
        rrdtool_create($rrd_filename, " --step 300 DS:mnc:GAUGE:600:0:U".$config['rrd_rra']);
    }
    $fields = array(
        'mnc' => $mnc,
    );
    rrdtool_update($rrd_filename, $fields);
    $graphs['cisco_wwan_mnc'] = TRUE;
    unset($rrd_filename,$mnc);
}
