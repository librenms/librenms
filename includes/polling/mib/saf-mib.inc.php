<?php

echo ' SAF Tehnika ';

$mib_oids = array(
    'radioRxLevel' => array(
        'local',
        'radioRxLevel',
        'RX Power',
        'GAUGE',
    ),
    'radioTxPower' => array(
        'local',
        'radioTxPower',
        'TX Power',
        'GAUGE',
    ),
    'modemRadialMSE' => array(
        'local',
        'modemRadialMSE',
        'Radial MSE',
        'GAUGE',
    ),
    'modemACMtotalCapacity' => array(
        'local',
        'modemACMCapacity',
        'ACM Capacity',
        'GAUGE',
    ),
    'modemTotalCapacity' => array(
        'local',
        'modemTotalCapacity',
        'Capacity',
        'GAUGE',
    ),
);

$mib_graphs = array(
    'saf_radioRxLevel',
    'saf_radioTxPower',
    'saf_modemRadialMSE',
    'saf_modemCapacity',
);

unset($graph, $oids, $oid);

poll_mib_def($device, 'SAF-IPRADIO:saf-modem-radio', 'saf', $mib_oids, $mib_graphs, $graphs);
