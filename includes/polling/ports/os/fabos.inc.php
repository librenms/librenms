<?php

$sw_fc_port_table = \LibreNMS\Util\SnmpQuery::walk('SW-MIB::swFCPortTable')->valuesByIndex();

foreach ($sw_fc_port_table as $index => $brocade_port) {
    $index_brocade = $index + 1073741823;

    if (isset($brocade_port['SW-MIB::swFCPortName'])) {
        $port_stats[$index_brocade]['ifAlias'] = $brocade_port['SW-MIB::swFCPortName'];
    }

    $if_in_errors =
        ($brocade_port['SW-MIB::swFCPortRxEncInFrs'] ?? 0) +
        ($brocade_port['SW-MIB::swFCPortRxCrcs'] ?? 0) +
        ($brocade_port['SW-MIB::swFCPortRxTruncs'] ?? 0) +
        ($brocade_port['SW-MIB::swFCPortRxTooLongs'] ?? 0) +
        ($brocade_port['SW-MIB::swFCPortRxBadEofs'] ?? 0) +
        ($brocade_port['SW-MIB::swFCPortRxEncOutFrs'] ?? 0) +
        ($brocade_port['SW-MIB::swFCPortRxBadOs'] ?? 0);

    $port_stats[$index_brocade]['ifInErrors'] = $if_in_errors;
}