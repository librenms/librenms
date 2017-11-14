<?php
/*
 * LibreNMS Pulse Secure OS information module
 *
 * Copyright (c) 2015 Christophe Martinet Chrisgfx <martinet.christophe@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
*/
use LibreNMS\RRD\RrdDefinition;

$version = $poll_device['sysDescr'];
$masterSlaveMode = ucfirst(snmp_get($device, 'masterSlaveMode.0', '-Oqv', "CAMBIUM-PTP650-MIB"));
$hardware = 'PTP 650 '. $masterSlaveMode;

$ssr = snmp_get($device, "signalStrengthRatio.0", "-Ovqn", "CAMBIUM-PTP650-MIB");
if (is_numeric($ssr)) {
    $rrd_def = RrdDefinition::make()->addDataset('ssr', 'GAUGE', -150, 150);
    $fields = array(
        'ssr' => $ssr,
    );
    $tags = compact('rrd_def');
    data_update($device, 'cambium-650-ssr', $tags, $fields);
    $graphs['cambium_650_ssr'] = true;
}
