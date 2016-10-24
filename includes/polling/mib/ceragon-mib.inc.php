<?php
$mib_oids = array();

$radioNumber = 0;
$IfIndex = 0;

$IfNumber = snmp_get_next($device, "ifNumber", "-mIF-MIB -Oqv", "");

for ($i=0; $i < $IfNumber; $i++) {
if ($IfIndex == "0") {
    $IfIndex = snmp_get_next($device, "ifIndex", "-mIF-MIB -Oqv", "");
} else {
    $IfIndex = snmp_get_next($device, "ifIndex.$IfIndex", "-mIF-MIB -Oqv", "");
}
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
    $mib_oids["genEquipRadioStatusXPI.$IfIndex"] = array(
            "",
            "radio".$radioNumber."XPI",
            $IfName." Cross Polarisation Interference",
            "GAUGE",
        );
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


