<?php

$index = 0;


if (strtok(snmp_get_next($device, "genEquipRadioCfgRadioId.".$index, "-mMWRM-RADIO-MIB -OsqnU", ""), " ") === strtok(snmp_get_next($device, "genEquipRadioCfgRadioId", "-mMWRM-RADIO-MIB -OsqnU", ""), " ")) {

echo "In loop ... " . $index . " \n";
    $radioslot_id = snmp_get_next($device, "genEquipRadioCfgRadioId.".$index, "-mMWRM-RADIO-MIB -Oqv", "");

    $mib_oids = array(
        "genEquipRfuStatusRxLevel.".$radioslot_id => array(
            "",
            "radio".$index."RxLevel",
            "Radio ".$index." RX Level",
            "GAUGE",
        ),
        "genEquipRfuStatusTxLevel.".$radioslot_id => array(
            "",
            "radio".$index."TxPower",
            "Radio ".$index." TX Power",
            "GAUGE",
        ),
        "genEquipRadioStatusMSE.".$radioslot_id => array(
            "",
            "radio".$index."MSE",
            "Radio ".$index." MSE",
            "GAUGE",
        ),
        "genEquipRadioStatusXPI.".$radioslot_id => array(
            "",
            "radio".$index."XPI",
            "Radio ".$index." Cross Polarisation Interference",
            "GAUGE",
        ),
        "genEquipRadioStatusDefectedBlocks.".$radioslot_id => array(
            "",
            "radio".$index."DefectedBlocks",
            "Radio ".$index." Defected Blocks",
            "GAUGE",
        ),
        "genEquipRadioMRMCCurrTxBitrate.".$radioslot_id => array(
            "",
            "radio".$index."TxRate",
            "Radio ".$index." Tx Bit Rate",
            "GAUGE",
        ),
        "genEquipRadioMRMCCurrRxBitrate.".$radioslot_id => array(
            "",
            "radio".$index."RxRate",
            "Radio ".$index." Rx Bit Rate",
            "GAUGE",
        ),
    );
    $index = $index+1;
}

    $mib_graphs = array(
        "ceragon_RxLevel",
        "ceragon_TxPower",
        "ceragon_MSE",
        "ceragon_XPI",
        "ceragon_DefectedBlocks",
        "ceragon_TxBitrate",
        "ceragon_RxBitrate",
    );

    unset($graph, $oids, $oid);

    poll_mib_def($device, "MWRM-RADIO-MIB:ceragon-radio", "ceragon", $mib_oids, $mib_graphs, $graphs);


