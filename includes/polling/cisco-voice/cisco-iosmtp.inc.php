<?php
/*
 * LibreNMS module to Graph Hardware MTP Resources in a Cisco Voice Router
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
    $total = snmpwalk_cache_oid_num ($device, "1.3.6.1.4.1.9.9.86.1.6.4.1.3", NULL);
    $total = $total['1.3.6.1.4.1.9.9.86.1.6.4.1.3'][''];

    if (isset($total) && ($total != "") && ($total != 0)) {
        // Available
        $available = snmpwalk_cache_oid_num ($device, "1.3.6.1.4.1.9.9.86.1.6.4.1.4", NULL);
        $available = $available['1.3.6.1.4.1.9.9.86.1.6.4.1.4'][''];

        // Active
        $active = $total - $available;

        $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename ("cisco-iosmtp.rrd");
        if (!file_exists ($rrd_filename)) {
            rrdtool_create ($rrd_filename, " DS:total:GAUGE:600:0:U DS:active:GAUGE:600:0:U" . $config['rrd_rra']);
        }

        $fields = array(
            'total'  => $total,
            'active' => $active,
        );

        rrdtool_update ($rrd_filename, $fields);

        $graphs['cisco-iosmtp'] = TRUE;
        echo (" Cisco IOS MTP ");
    }
    unset($rrd_filename, $total, $active, $available);
}
