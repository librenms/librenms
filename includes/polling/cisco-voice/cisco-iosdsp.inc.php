<?php
/*
 * LibreNMS module to Graph Digital Signal Processor (DSP) Resources in a Cisco Voice Router
 *
 * Copyright (c) 2015 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os_group'] == "cisco") {
    // Total
    $total = 0;
    foreach (snmpwalk_cache_oid_num ($device, "1.3.6.1.4.1.9.9.86.1.2.1.1.6", NULL) as $key => $value) {
        $total += $value[''];
    }

    if (isset($total) && ($total != "") && ($total != 0)) {
        // Active
        $active = 0;
        foreach ( snmpwalk_cache_oid_num ($device, "1.3.6.1.4.1.9.9.86.1.2.1.1.7", NULL) as $key => $value) {
            $active += $value[''];
        }

        $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename ("cisco-iosdsp.rrd");
        if (!file_exists ($rrd_filename)) {
            rrdtool_create ($rrd_filename, " DS:total:GAUGE:600:0:U DS:active:GAUGE:600:0:U" . $config['rrd_rra']);
        }

        $fields = array(
            'total'  => $total,
            'active' => $active,
        );

        rrdtool_update ($rrd_filename, $fields);

        $tags = array();
        influx_update($device,'cisco-iosdsp',$tags,$fields);

        $graphs['cisco-iosdsp'] = TRUE;
        echo (" Cisco IOS DSP ");
    }
    unset($rrd_filename, $total, $active);
}
