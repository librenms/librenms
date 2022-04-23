<?php

use LibreNMS\RRD\RrdDefinition;

$vp_rows = dbFetchRows('SELECT * FROM `ports` AS P, `juniAtmVp` AS J WHERE P.`device_id` = ? AND J.port_id = P.port_id', [$device['device_id']]);

if (count($vp_rows)) {
    $vp_cache = [];
    $vp_cache = snmpwalk_cache_multi_oid($device, 'juniAtmVpStatsInCells', $vp_cache, 'Juniper-UNI-ATM-MIB', 'junose');
    $vp_cache = snmpwalk_cache_multi_oid($device, 'juniAtmVpStatsInPackets', $vp_cache, 'Juniper-UNI-ATM-MIB', 'junose');
    $vp_cache = snmpwalk_cache_multi_oid($device, 'juniAtmVpStatsInPacketOctets', $vp_cache, 'Juniper-UNI-ATM-MIB', 'junose');
    $vp_cache = snmpwalk_cache_multi_oid($device, 'juniAtmVpStatsInPacketErrors', $vp_cache, 'Juniper-UNI-ATM-MIB', 'junose');
    $vp_cache = snmpwalk_cache_multi_oid($device, 'juniAtmVpStatsOutCells', $vp_cache, 'Juniper-UNI-ATM-MIB', 'junose');
    $vp_cache = snmpwalk_cache_multi_oid($device, 'juniAtmVpStatsOutPackets', $vp_cache, 'Juniper-UNI-ATM-MIB', 'junose');
    $vp_cache = snmpwalk_cache_multi_oid($device, 'juniAtmVpStatsOutPacketOctets', $vp_cache, 'Juniper-UNI-ATM-MIB', 'junose');
    $vp_cache = snmpwalk_cache_multi_oid($device, 'juniAtmVpStatsOutPacketErrors', $vp_cache, 'Juniper-UNI-ATM-MIB', 'junose');

    $rrd_def = RrdDefinition::make()
        ->addDataset('incells', 'DERIVE', 0, 125000000000)
        ->addDataset('outcells', 'DERIVE', 0, 125000000000)
        ->addDataset('inpackets', 'DERIVE', 0, 125000000000)
        ->addDataset('outpackets', 'DERIVE', 0, 125000000000)
        ->addDataset('inpacketoctets', 'DERIVE', 0, 125000000000)
        ->addDataset('outpacketoctets', 'DERIVE', 0, 125000000000)
        ->addDataset('inpacketerrors', 'DERIVE', 0, 125000000000)
        ->addDataset('outpacketerrors', 'DERIVE', 0, 125000000000);

    foreach ($vp_rows as $vp) {
        echo '.';

        $ifIndex = $vp['ifIndex'];
        $vp_id = $vp['vp_id'];
        $oid = $ifIndex . '.' . $vp_id;

        d_echo("$oid ");

        $t_vp = $vp_cache[$oid];

        $vp_update = $t_vp['juniAtmVpStatsInCells'] . ':' . $t_vp['juniAtmVpStatsOutCells'];
        $vp_update .= ':' . $t_vp['juniAtmVpStatsInPackets'] . ':' . $t_vp['juniAtmVpStatsOutPackets'];
        $vp_update .= ':' . $t_vp['juniAtmVpStatsInPacketOctets'] . ':' . $t_vp['juniAtmVpStatsOutPacketOctets'];
        $vp_update .= ':' . $t_vp['juniAtmVpStatsInPacketErrors'] . ':' . $t_vp['juniAtmVpStatsOutPacketErrors'];

        $rrd_name = ['vp', $ifIndex, $vp_id];

        $fields = [
            'incells'         => $t_vp['juniAtmVpStatsInCells'],
            'outcells'        => $t_vp['juniAtmVpStatsOutCells'],
            'inpackets'       => $t_vp['juniAtmVpStatsInPackets'],
            'outpackets'      => $t_vp['juniAtmVpStatsOutPackets'],
            'inpacketoctets'  => $t_vp['juniAtmVpStatsInPacketOctets'],
            'outpacketoctets' => $t_vp['juniAtmVpStatsOutPacketOctets'],
            'inpacketerrors'  => $t_vp['juniAtmVpStatsInPacketErrors'],
            'outpacketerrors' => $t_vp['juniAtmVpStatsOutPacketErrors'],
        ];

        $tags = compact('ifIndex', 'vp_id', 'rrd_name', 'rrd_def');
        data_update($device, 'atm-vp', $tags, $fields);
    }//end foreach

    echo "\n";

    unset($vp_cache, $rrd_def);
}//end if
