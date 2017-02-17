<?php

/**
 * @copyright  (C) 2015 Mark Gibbons
 */

// Polling of AirFIBER MIB AP for Ubiquiti AirFIBER Radio Rx Levels
//
// UBNT-AirFIBER-MIB
echo ' UBNT-AirFIBER - Rx';

$rxPower = snmp_get_multi($device, "rxPower0.1 rxPower1.1", "-OQUs", "UBNT-AirFIBER-MIB");
d_echo($rxPower);
$rxPower0 = $rxPower[1]["rxPower0"];
$rxPower1 = $rxPower[1]["rxPower1"];
    $rrd_def = array(
            'DS:rxPower0:GAUGE:600:-105:0 ',
            'DS:rxPower1:GAUGE:600:-105:0 '
    );
    $fields = array(
            'rxPower0' => $rxPower0,
            'rxPower1' => $rxPower1
    );
    $tags = compact('rrd_def');
    data_update($device, 'ubnt-airfiber-rx', $tags, $fields);
    $graphs['ubnt_airfiber_RxPower'] = true;
    unset($rrd_def, $rxPower0, $rxPower1, $rxPower, $fields, $tags);
// EOF
