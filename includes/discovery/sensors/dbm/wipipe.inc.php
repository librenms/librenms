<?php

/*
 * LibreNMS Interface Modem Signal Strength dBm module for the CradlePoint WiPipe Platform
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

$multiplier = 1;
$divisor = 1;

foreach ($pre_cache['wipipe_oids'] as $index => $entry) {
    // Modem Signal Strength
    if ($entry['mdmSignalStrength']) {
        $oid = '.1.3.6.1.4.1.20992.1.2.2.1.4.' . $index;
        // Get Modem Model & Phone Number for description
        $modemdesc = $entry['mdmDescr'];
        $modemmdn = $entry['mdmMDN'];
        $descr = 'Signal Strength - ' . $modemdesc . ' - ' . $modemmdn;
        $currentsignal = $entry['mdmSignalStrength'];
        // Discover Sensor
        discover_sensor(
            $valid['sensor'],
            'dbm',
            $device,
            $oid,
            'mdmSignalStrength.' . $index,
            'wipipe',
            $descr,
            $divisor,
            $multiplier,
            null,
            null,
            null,
            null,
            $currentsignal,
            'snmp'
        );
    }
}
