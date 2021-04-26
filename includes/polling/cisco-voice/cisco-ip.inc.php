<?php
/*
 * LibreNMS module to IP active connections in a Cisco Voice Router
 *
 * Copyright (c) 2019 PipoCanaja
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

use LibreNMS\RRD\RrdDefinition;

if ($device['os_group'] == 'cisco') {
    $output = snmpwalk_cache_oid($device, 'cvCallVolConnActiveConnection', [], 'CISCO-VOICE-DIAL-CONTROL-MIB');
    d_echo($output);
    if (is_array($output)) {
        $rrd_def = RrdDefinition::make()
            ->addDataset('h323', 'GAUGE', 0)
            ->addDataset('sip', 'GAUGE', 0)
            ->addDataset('mgcp', 'GAUGE', 0)
            ->addDataset('sccp', 'GAUGE', 0)
            ->addDataset('multicast', 'GAUGE', 0);

        $fields = [
            'h323' => $output['h323']['cvCallVolConnActiveConnection'],
            'sip' => $output['sip']['cvCallVolConnActiveConnection'],
            'mgcp' => $output['mgcp']['cvCallVolConnActiveConnection'],
            'sccp' => $output['sccp']['cvCallVolConnActiveConnection'],
            'multicast' => $output['multicast']['cvCallVolConnActiveConnection'],
        ];
        d_echo($fields);
        $tags = compact('rrd_def');
        data_update($device, 'cisco-voice-ip', $tags, $fields);

        $os->enableGraph('cisco-voice-ip');
        echo ' Cisco IOS Voice IP ';
        unset($rrd_def, $active, $fields, $tags);
    }
}
