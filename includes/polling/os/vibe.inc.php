<?php
/*
 * LibreNMS Vibe OS information module
 *
 * Copyright (c) 2018 Craig Askings <caskings@ionetworks.com.au>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
*/

use LibreNMS\RRD\RrdDefinition;

$global_rtp = snmp_get($device, 'vibeStatusRTP.0', '-OQv', 'VOIPEX-VIBE-MIB');

if (is_numeric($global_rtp)) {
    $rrd_def = RrdDefinition::make()->addDataset('global_rtp', 'GAUGE', 0);

    $fields = array(
        'global_rtp' => $global_rtp,
    );

    $tags = compact('rrd_def');
    data_update($device, 'vibe_global_rtp', $tags, $fields);
    $graphs['vibe_global_rtp'] = true;
}

$vibe_peers= snmp_get($device, 'vibeConfigPeers.0', '-OQv', 'VOIPEX-VIBE-MIB');

if (is_numeric($vibe_peers)) {
    $rrd_def = RrdDefinition::make()->addDataset('vibe_peers', 'GAUGE', 0);

    $fields = array(
        'vibe_peers' => $vibe_peers,
    );

    $tags = compact('rrd_def');
    data_update($device, 'vibe_global_rtp', $tags, $fields);
    $graphs['vibe_peers'] = true;
}
