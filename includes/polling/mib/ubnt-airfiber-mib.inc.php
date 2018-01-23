<?php

/**
 * @copyright  (C) 2015 Mark Gibbons
 */

// Polling of AirFIBER MIB AP for Ubiquiti AirFIBER Radios
//
// UBNT-AirFIBER-MIB
echo ' UBNT-AirFIBER-MIB ';

// $mib_oids                                (oidindex,dsname,dsdescription,dstype)
$mib_oids = array(
    'txFrequency'             => array(
        '1',
        'txFrequency',
        'Tx Frequency',
        'GAUGE',
    ),
    'rxFrequency'             => array(
        '1',
        'rxFrequency',
        'Rx Frequency',
        'GAUGE',
    ),
    'txPower'                 => array(
        '1',
        'txPower',
        'Tx Power',
        'GAUGE',
    ),
    'radioLinkDistM'          => array(
        '1',
        'radioLinkDistM',
        'Link Distance',
        'GAUGE',
    ),
    'rxCapacity'              => array(
        '1',
        'rxCapacity',
        'Rx Capacity',
        'GAUGE',
    ),
    'txCapacity'              => array(
        '1',
        'txCapacity',
        'Tx Capacity',
        'GAUGE',
    ),
    'radio0TempC'             => array(
        '1',
        'radio0TempC',
        'Radio 0 Temp',
        'GAUGE',
    ),
    'radio1TempC'             => array(
        '1',
        'radio1TempC',
        'Radio 1 Temp',
        'GAUGE',
    ),
    // above here is duplicated in wireless
    'txOctetsOK'              => array(
        '1',
        'txOctetsOK',
        'Tx Octets OK',
        'COUNTER',
    ),
    'rxOctetsOK'              => array(
        '1',
        'rxOctetsOK',
        'Rx Octets OK',
        'COUNTER',
    ),
    'rxValidUnicastFrames'    => array(
        '1',
        'rxValUnicastFrms',
        'TODOa',
        'COUNTER',
    ),
    'rxValidMulticastFrames'  => array(
        '1',
        'rxValMulticastFrms',
        'TODOa',
        'COUNTER',
    ),
    'rxValidBroadcastFrames'  => array(
        '1',
        'rxValBroadcastFrms',
        'TODO',
        'COUNTER',
    ),
    'txValidUnicastFrames'    => array(
        '1',
        'txValUnicastFrms',
        'TODO',
        'COUNTER',
    ),
    'txValidMulticastFrames'  => array(
        '1',
        'txValMulticastFrms',
        'TODO',
        'COUNTER',
    ),
    'txValidBroadcastFrames'  => array(
        '1',
        'txValBroadcastFrms',
        'TODO',
        'COUNTER',
    ),
    'rxTotalOctets'           => array(
        '1',
        'rxTotalOctets',
        'TODO',
        'COUNTER',
    ),
    'rxTotalFrames'           => array(
        '1',
        'rxTotalFrms',
        'TODO',
        'COUNTER',
    ),
    'rx64BytePackets'         => array(
        '1',
        'rx64BytePkts',
        'TODO',
        'COUNTER',
    ),
    'rx65-127BytePackets'     => array(
        '1',
        'rx65-127BytePkts',
        'TODO',
        'COUNTER',
    ),
    'rx128-255BytePackets'    => array(
        '1',
        'rx128-255BytePkts',
        'TODO',
        'COUNTER',
    ),
    'rx256-511BytePackets'    => array(
        '1',
        'rx256-511BytePkts',
        'TODO',
        'COUNTER',
    ),
    'rx512-1023BytePackets'   => array(
        '1',
        'rx512-1023BytePkts',
        'TODO',
        'COUNTER',
    ),
    'rx1024-1518BytesPackets' => array(
        '1',
        'rx1024-1518BytePkts',
        'TODO',
        'COUNTER',
    ),
    'rx1519PlusBytePackets'   => array(
        '1',
        'rx1519PlusBytePkts',
        'TODO',
        'COUNTER',
    ),
    'txoctetsAll'             => array(
        '1',
        'txoctetsAll',
        'TODO',
        'COUNTER',
    ),
    'txpktsAll'               => array(
        '1',
        'txpktsAll',
        'TODO',
        'COUNTER',
    ),
    'rxoctetsAll'             => array(
        '1',
        'rxoctetsAll',
        'TODO',
        'COUNTER',
    ),
    'rxpktsAll'               => array(
        '1',
        'rxpktsAll',
        'TODO',
        'COUNTER',
    ),
);


$mib_graphs = array(
    'ubnt_airfiber_RadioFreqs',
    'ubnt_airfiber_TxPower',
    'ubnt_airfiber_LinkDist',
    'ubnt_airfiber_Capacity',
    'ubnt_airfiber_RadioTemp',
    'AF1',
    'AF2',
    'AF3',
    'AF4',
    'AF5',
    'ubnt_airfiber_RFTotOctetsTx',
    'ubnt_airfiber_RFTotPktsTx',
    'ubnt_airfiber_RFTotOctetsRx',
    'ubnt_airfiber_RFTotPktsRx',
);

unset($graph, $oids, $oid);

poll_mib_def($device, 'UBNT-AirFIBER-MIB:UBNT', 'ubiquiti', $mib_oids, $mib_graphs, $graphs);
