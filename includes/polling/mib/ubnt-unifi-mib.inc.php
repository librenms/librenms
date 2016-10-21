<?php
// Polling of UniFi MIB AP for Ubiquiti Unifi Radios
// based on Airfiber MIB work of Mark Gibbons

// UBNT-UniFi-MIB
echo ' UBNT-UniFi-MIB ';

// $mib_oids     (oidindex,dsname,dsdescription,dstype)
$mib_oids = array(
    'unifiRadioCuTotal.0'                 => array(
        '',
        'Radio0CuTotal',
        'Radio0 Channel Utilized',
        'GAUGE',
    ),
    'unifiRadioCuTotal.1'                 => array(
        '',
        'Radio1CuTotal',
        'Radio1 Channel Utilized',
        'GAUGE',
    ),
    'unifiRadioCuSelfRx.0'                 => array(
        '',
        'Radio0CuSelfRx',
        'Radio0 Channel Utilized Rx',
        'GAUGE',
    ),
    'unifiRadioCuSelfRx.1'                 => array(
        '',
        'Radio1CuSelfRx',
        'Radio1 Channel Utilized Rx',
        'GAUGE',
    ),
    'unifiRadioCuSelfTx.0'                 => array(
        '',
        'Radio0CuSelfTx',
        'Radio0 Channel Utilized Tx',
        'GAUGE',
    ),
    'unifiRadioCuSelfTx.1'                 => array(
        '',
        'Radio1CuSelfTx',
        'Radio1 Channel Utilized Tx',
        'GAUGE',
    ),
    'unifiRadioOtherBss.0'                 => array(
        '',
        'Radio0OtherBss',
        'Radio0 Channel Utilized by Others',
        'GAUGE',
    ),
    'unifiRadioOtherBss.1'                 => array(
        '',
        'Radio1OtherBss',
        'Radio1 Channel Utilized by Others',
        'GAUGE',
    ),
);


$mib_graphs = array(
    'ubnt_unifi_RadioCu_0',
    'ubnt_unifi_RadioCu_1',
);

unset($graph, $oids, $oid);

poll_mib_def($device, 'UBNT-UniFi-MIB:UBNT', 'ubiquiti', $mib_oids, $mib_graphs, $graphs);
// EOF
