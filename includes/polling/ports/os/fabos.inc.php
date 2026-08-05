<?php

foreach (snmpwalk_group($device, 'swFCPortName', 'SW-MIB') as $index => $brocade_port) {
    $index_brocade = $index + 1073741823;
    $port_stats[$index_brocade]['ifAlias'] = $brocade_port['swFCPortName'];
}

$brocade_fc_error_oids = [
    'swFCPortRxEncInFrs',
    'swFCPortRxCrcs',
    'swFCPortRxTruncs',
    'swFCPortRxTooLongs',
    'swFCPortRxBadEofs',
    'swFCPortRxEncOutFrs',
    'swFCPortRxBadOs',
];

$brocade_fc_port_stats = [];

foreach ($brocade_fc_error_oids as $oid) {
    foreach (snmpwalk_group($device, $oid, 'SW-MIB') as $index => $data) {
        if (isset($data[$oid]) && is_numeric($data[$oid])) {
            $brocade_fc_port_stats[$index][$oid] = (int) $data[$oid];
        }
    }
}

foreach ($brocade_fc_port_stats as $index => $stats) {
    $index_brocade = $index + 1073741823;

    /*
     * Map FC physical/protocol receive errors into standard interface input errors.
     */
    $if_in_errors =
        ($stats['swFCPortRxEncInFrs'] ?? 0) +
        ($stats['swFCPortRxCrcs'] ?? 0) +
        ($stats['swFCPortRxTruncs'] ?? 0) +
        ($stats['swFCPortRxTooLongs'] ?? 0) +
        ($stats['swFCPortRxBadEofs'] ?? 0) +
        ($stats['swFCPortRxEncOutFrs'] ?? 0) +
        ($stats['swFCPortRxBadOs'] ?? 0);


    $port_stats[$index_brocade]['ifInErrors'] = $if_in_errors;
}