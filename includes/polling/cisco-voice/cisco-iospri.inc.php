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

if ($device['os_group'] == "cisco") {
    // TODO: Need to test partial PRI.

    // Total
    $total = 0;
    foreach ( snmpwalk_cache_oid_num ($device, "1.3.6.1.2.1.2.2.1.3", NULL) as $key => $value) {
        // 81 is the ifType for DS0's
        if ($value[''] == "81") {
            $total++;
        }
    }

    // Active
    $active = snmpwalk_cache_oid_num ($device, "1.3.6.1.4.1.9.10.19.1.1.4.0", NULL);
    $active = $active['1.3.6.1.4.1.9.10.19.1.1.4.0'][''];

    if (isset($active) && ($active != "") && ($total != 0)) {
        $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename ("cisco-iospri.rrd");

        if (!file_exists ($rrd_filename)) {
            rrdtool_create ($rrd_filename, " DS:total:GAUGE:600:0:U DS:active:GAUGE:600:0:U" . $config['rrd_rra']);
        }

        $fields = array(
            'total'  => $total,
            'active' => $active,
        );

        rrdtool_update ($rrd_filename, $fields);

        $graphs['cisco-iospri'] = TRUE;
        echo (" Cisco IOS PRI ");
    }
    unset($rrd_filename, $total, $active);
}
