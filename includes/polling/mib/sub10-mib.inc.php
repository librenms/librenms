<?php

echo ' Sub10 Systems';

$mib_oids = array(
    'sub10RadioLclTxPower' => array(
        '0',
        'sub10RadioLclTxPower',
        'Transmit Power',
        'GAUGE',
    ),
    'sub10RadioLclRxPower' => array(
        '0',
        'sub10RadioLclRxPower',
        'Receive Power',
        'GAUGE',
    ),
    'sub10RadioLclVectErr' => array(
        '0',
        'sub10RadioLclVectErr',
        'Vector Error',
        'GAUGE',
    ),
    'sub10RadioLclLnkLoss' => array(
        '0',
        'sub10RadioLclLnkLoss',
        'Link Loss',
        'GAUGE',
    ),
    'sub10RadioLclAFER' => array(
        '0',
        'sub10RadioLclAFER',
        'Air Frame Error Rate',
        'GAUGE',
    ),
    'sub10RadioLclDataRate' => array(
        '0',
        'sub10RadioLclDataRate',
        'Data Rate on Airside interface',
        'GAUGE',
    ),
);

$mib_graphs = array(
    'sub10_sub10RadioLclTxPower',
    'sub10_sub10RadioLclRxPower',
    'sub10_sub10RadioLclVectErr',
    'sub10_sub10RadioLclLnkLoss',
    'sub10_sub10RadioLclAFER',
    'sub10RadioLclDataRate'
);

unset($graph, $oids, $oid);

poll_mib_def($device, 'SUB10SYSTEMS-MIB:sub10Systems', 'sub10', $mib_oids, $mib_graphs, $graphs);
