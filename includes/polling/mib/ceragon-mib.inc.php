<?php

$ceragon_type = snmp_get($device, 'sysObjectID.0', '-mSNMPv2-MIB -Mmibs/ -Oqv', '');
if (strstr($ceragon_type, '.2281.1.10')) {
    echo ' Ceragon IP10 ';
    
// single radio only on most IP10 (ecxcept IP10G), also uses different MIBs
    echo ' Not yet supported ';
} elseif (strstr($ceragon_type, '.2281.1.20.1.1')) {
    echo ' Ceragon IP20N, A or Evolution 1u IDU ';
    
// multiple slots possible on IP20N/A/Evolution, need to get oids for them
    echo ' Not yet supported ';
} elseif (strstr($ceragon_type, '.2281.1.20.1.2')) {
    echo ' Ceragon IP20N, A or Evolution 2u IDU ';
    
// multiple slots possible on IP20N/A/Evolution, need to get oids for them
    echo ' Not yet supported ';
} elseif (strstr($ceragon_type, '.2281.1.20.1.3')) {
    echo ' Ceragon IP20G or GX ';
    
// multiple slots possible on IP20G(X), need to get oids for them
    echo ' Not yet supported ';
    
} elseif (strstr($ceragon_type, '.2281.1.20.2.2')) {
    echo ' Ceragon IP20C, E or S ';

// two radios on IP20C, E or S. need to get sub-oid for each.
    $radioslot1_id = snmp_get_next($device, 'genEquipRadioCfgRadioId', '-mMWRM-RADIO-MIB -Oqv', '');
    $radioslot2_id = snmp_get_next($device, 'genEquipRadioCfgRadioId.'.$radioslot1_id, '-mMWRM-RADIO-MIB -Oqv', '');

    $mib_oids = array(
        'genEquipRfuStatusRxLevel.'.$radioslot1_id => array(
            '',
            'radio1RxLevel',
            'Radio 1 RX Level',
            'GAUGE',
        ),
        'genEquipRfuStatusTxLevel.'.$radioslot1_id => array(
            '',
            'radio1TxPower',
            'Radio 1 TX Power',
            'GAUGE',
        ),
        'genEquipRadioStatusMSE.'.$radioslot1_id => array(
            '',
            'radio1MSE',
            'Radio 1 MSE',
            'GAUGE',
        ),
        'genEquipRadioStatusXPI.'.$radioslot1_id => array(
            '',
            'radio1XPI',
            'Radio 1 Cross Polarisation Interference',
            'GAUGE',
        ),
        'genEquipRadioStatusDefectedBlocks.'.$radioslot1_id => array(
            '',
            'radio1DefectedBlocks',
            'Radio 1 Defected Blocks',
            'GAUGE',
        ),
        'genEquipRadioMRMCCurrTxBitrate.'.$radioslot1_id => array(
            '',
            'radio1TxRate',
            'Radio 1 Tx Bit Rate',
            'GAUGE',
        ),
        'genEquipRadioMRMCCurrRxBitrate.'.$radioslot1_id => array(
            '',
            'radio1RxRate',
            'Radio 1 Rx Bit Rate',
            'GAUGE',
        ),
        'genEquipRfuStatusRxLevel.'.$radioslot2_id => array(
            '',
            'radio2RxLevel',
            'Radio 2 RX Level',
            'GAUGE',
        ),
        'genEquipRfuStatusTxLevel.'.$radioslot2_id => array(
            '',
            'radio2TxPower',
            'Radio 2 TX Power',
            'GAUGE',
        ),
        'genEquipRadioStatusMSE.'.$radioslot2_id => array(
            '',
            'radio2MSE',
            'Radio 2 MSE',
            'GAUGE',
        ),
        'genEquipRadioStatusXPI.'.$radioslot2_id => array(
            '',
            'radio2XPI',
            'Radio 2 Cross Polarisation Interference',
            'GAUGE',
        ),
        'genEquipRadioStatusDefectedBlocks.'.$radioslot2_id => array(
            '',
            'radio2DefectedBlocks',
            'Radio 2 Defected Blocks',
            'GAUGE',
        ),
        'genEquipRadioMRMCCurrTxBitrate.'.$radioslot2_id => array(
            '',
            'radio2TxRate',
            'Radio 2 Tx Bit Rate',
            'GAUGE',
        ),
        'genEquipRadioMRMCCurrRxBitrate.'.$radioslot2_id => array(
            '',
            'radio2RxRate',
            'Radio 2 Rx Bit Rate',
            'GAUGE',
        ),
    );

    $mib_graphs = array(
        'ceragon_RxLevel',
        'ceragon_TxPower',
        'ceragon_MSE',
        'ceragon_XPI',
        'ceragon_DefectedBlocks',
        'ceragon_TxBitrate',
        'ceragon_RxBitrate',
    );

    unset($graph, $oids, $oid);

    poll_mib_def($device, 'MWRM-RADIO-MIB:ceragon-radio', 'ceragon', $mib_oids, $mib_graphs, $graphs);
};

