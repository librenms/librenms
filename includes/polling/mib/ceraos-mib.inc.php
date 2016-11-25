<?php

$features = explode(' ', $device[features]);
$num_radios = $features[0];

$mib_oids = array();

$radioNumber = 0;
$IfIndex = 0;
$ifIndexesArray = array();
$ifIndexesArray = explode("\n", snmp_walk($device, "ifIndex", "-mIF-MIB -Oqv", ""));

foreach ($ifIndexesArray as $IfIndex) {
    $IfDescr = snmp_get($device, "ifDescr.$IfIndex", "-mIF-MIB -Oqv", "");
    $IfName = snmp_get($device, "ifName.$IfIndex", "-mIF-MIB -Oqv", "");
    if (stristr($IfDescr, "Radio")) {
        $radioNumber = $radioNumber+1;

        $mib_oids["genEquipRfuStatusRxLevel.$IfIndex"] = array(
            "",
            "radio".$radioNumber."RxLevel",
            $IfName." RX Level",
            "GAUGE",
        );
        $mib_oids["genEquipRfuStatusTxLevel.$IfIndex"] = array(
            "",
            "radio".$radioNumber."TxPower",
            $IfName." TX Power",
            "GAUGE",
        );
        $mib_oids["genEquipRadioStatusMSE.$IfIndex"] = array(
            "",
            "radio".$radioNumber."MSE",
            $IfName." MSE",
            "GAUGE",
        );
        if ($num_radios > 1) {
            $mib_oids["genEquipRadioStatusXPI.$IfIndex"] = array(
                "",
                "radio".$radioNumber."XPI",
                $IfName." Cross Polarisation Interference",
                "GAUGE",
            );
        }
        $mib_oids["genEquipRadioStatusDefectedBlocks.$IfIndex"] = array(
            "",
            "radio".$radioNumber."DefectedBlocks",
            $IfName." Defected Blocks",
            "GAUGE",
        );
        $mib_oids["genEquipRadioMRMCCurrTxBitrate.$IfIndex"] = array(
            "",
            "radio".$radioNumber."TxRate",
            $IfName." Tx Bit Rate",
            "GAUGE",
        );
        $mib_oids["genEquipRadioMRMCCurrRxBitrate.$IfIndex"] = array(
            "",
            "radio".$radioNumber."RxRate",
            $IfName." Rx Bit Rate",
            "GAUGE",
        );
    }
}
if ($num_radios > 1) {
    $mib_graphs = array(
        "ceraos_RxLevel",
        "ceraos_TxPower",
        "ceraos_MSE",
        "ceraos_XPI",
        "ceraos_DefectedBlocks",
        "ceraos_TxBitrate",
        "ceraos_RxBitrate",
    );
} else {
    $mib_graphs = array(
        "ceraos_RxLevel",
        "ceraos_TxPower",
        "ceraos_MSE",
        "ceraos_DefectedBlocks",
        "ceraos_TxBitrate",
        "ceraos_RxBitrate",
    );
}
unset($graph, $oids, $oid);
poll_mib_def($device, "MWRM-RADIO-MIB:ceragon-radio", "ceraos", $mib_oids, $mib_graphs, $graphs);
