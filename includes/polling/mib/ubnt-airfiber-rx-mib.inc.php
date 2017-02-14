<?php

/**
 * @copyright  (C) 2015 Mark Gibbons
 */

// Polling of AirFIBER MIB AP for Ubiquiti AirFIBER Radios
//
// UBNT-AirFIBER-MIB
echo ' UBNT-AirFIBER-MIB - Rx';

// $mib_oids                                (oidindex,dsname,dsdescription,dstype)
$mib_oids = array(
    'rxPower0'                 => array(
        '1',
        'rxPower0',
        'Rx Chain0 Power',
        'GAUGE',
    ),
    'rxPower1'                 => array(
        '1',
        'rxPower1',
        'Rx Chain1 Power',
        'GAUGE',
    ),
);


$mib_graphs = array(
    'ubnt_airfiber_RxPower',
);

unset($graph, $oids, $oid);

poll_mib_def($device, 'UBNT-AirFIBER-MIB:UBNT', 'ubiquiti', $mib_oids, $mib_graphs, $graphs);
unset($mib_graphs, $mib_oids);
// EOF
