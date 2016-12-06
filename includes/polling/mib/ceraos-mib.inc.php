<?php

$features = explode(' ', $device[features]);
$num_radios = $features[0];

$mib_oids = array();

$radioNumber = 0;
$ifIndex = 0;
$ifIndex_array = array();
$ifIndex_array = explode("\n", snmp_walk($device, "ifIndex", "-Oqv", "IF-MIB"));

$snmp_get_oids = "";
foreach ($ifIndex_array as $ifIndex) {
    $snmp_get_oids .= "ifDescr.$ifIndex ifName.$ifIndex ";
}

$ifDescr_array = array();
$ifDescr_array = snmp_get_multi($device, $snmp_get_oids, '-OQU', 'IF-MIB');
d_echo($ifDescr_array);
foreach ($ifIndex_array as $ifIndex) {
    d_echo("\$ifDescr_array[$ifIndex]['IF-MIB::ifDescr'] = " . $ifDescr_array[$ifIndex]['IF-MIB::ifDescr'] . "\n");
    $ifDescr = $ifDescr_array[$ifIndex]['IF-MIB::ifDescr'];
    d_echo("\$ifDescr_array[$ifIndex]['IF-MIB::ifName'] = " . $ifDescr_array[$ifIndex]['IF-MIB::ifName'] . "\n");
    $ifName = $ifDescr_array[$ifIndex]['IF-MIB::ifName'];
    if (stristr($ifDescr, "Radio")) {
        $radioNumber = $radioNumber+1;

        $mib_oids["genEquipRfuStatusRxLevel.$ifIndex"] = array(
            "",
            "radio".$radioNumber."RxLevel",
            $ifName." RX Level",
            "GAUGE",
        );
        $mib_oids["genEquipRfuStatusTxLevel.$ifIndex"] = array(
            "",
            "radio".$radioNumber."TxPower",
            $ifName." TX Power",
            "GAUGE",
        );
        $mib_oids["genEquipRadioStatusMSE.$ifIndex"] = array(
            "",
            "radio".$radioNumber."MSE",
            $ifName." MSE",
            "GAUGE",
        );
        if ($num_radios > 1) {
            $mib_oids["genEquipRadioStatusXPI.$ifIndex"] = array(
                "",
                "radio".$radioNumber."XPI",
                $ifName." Cross Polarisation Interference",
                "GAUGE",
            );
        }
        $mib_oids["genEquipRadioStatusDefectedBlocks.$ifIndex"] = array(
            "",
            "radio".$radioNumber."DefectedBlocks",
            $ifName." Defected Blocks",
            "GAUGE",
        );
        $mib_oids["genEquipRadioMRMCCurrTxBitrate.$ifIndex"] = array(
            "",
            "radio".$radioNumber."TxRate",
            $ifName." Tx Bit Rate",
            "GAUGE",
        );
        $mib_oids["genEquipRadioMRMCCurrRxBitrate.$ifIndex"] = array(
            "",
            "radio".$radioNumber."RxRate",
            $ifName." Rx Bit Rate",
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
poll_mib_def($device, "MWRM-RADIO-MIB:ceragon-radio", "ceraos", $mib_oids, $mib_graphs, $graphs);
unset($feature, $num_radios, $radioNumber, $ifIndex, $ifIndex_array, $ifName, $ifDescr,  $mib_graphs, $mib_oids, $snmp_get_oids);
