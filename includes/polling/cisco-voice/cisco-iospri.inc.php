<?php
/*
 * LibreNMS module to Graph Primary Rate ISDN Resources in a Cisco Voice Router
 *
 * Copyright (c) 2015 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

use LibreNMS\RRD\RrdDefinition;

if ($device['os_group'] == 'cisco') {
    // TODO: Need to test partial PRI.

    // Total
    $total = 0;
    $output = snmpwalk_cache_oid_num($device, '1.3.6.1.2.1.2.2.1.3', null);
    if (is_array($output)) {
        foreach ($output as $key => $value) {
            // 81 is the ifType for DS0's
            if ($value[''] == '81' || $value[''] == 'ds0') {
                $total++;
            }
        }

        // Active
        $active = snmpwalk_cache_oid_num($device, '1.3.6.1.4.1.9.10.19.1.1.4.0', null);
        $active = $active['1.3.6.1.4.1.9.10.19.1.1.4.0'];

        if (is_array($active)) {
            $active = $active[''];
        }

        if (isset($total) && $total > 0) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('total', 'GAUGE', 0)
                ->addDataset('active', 'GAUGE', 0);

            $fields = [
                'total' => $total,
                'active' => $active,
            ];

            $tags = compact('rrd_def');
            data_update($device, 'cisco-iospri', $tags, $fields);

            $os->enableGraph('cisco-iospri');
            echo ' Cisco IOS PRI ';
        }
        unset($rrd_def, $total, $active, $fields, $tags);
    }
}
