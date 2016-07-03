<?php
/*
 * LibreNMS module to capture Cisco Class-Based QoS Details
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

    $module = 'Cisco-CBQOS';

    require_once 'includes/component.php';
    $component = new component();
    $options['filter']['type'] = array('=',$module);
    $options['filter']['disabled'] = array('=',0);
    $options['filter']['ignore'] = array('=',0);
    $components = $component->getComponents($device['device_id'],$options);

    // We only care about our device id.
    $components = $components[$device['device_id']];

    // Only collect SNMP data if we have enabled components
    if (count($components > 0)) {
        // Let's gather the stats..
        $tblcbQosClassMapStats = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.166.1.15.1.1', 2);

        // Loop through the components and extract the data.
        foreach ($components as $key => $array) {
            $type = $array['qos-type'];

            // Get data from the class table.
            if ($type == 2) {
                // Let's make sure the rrd is setup for this class.
                $filename = "port-".$array['ifindex']."-cbqos-".$array['sp-id']."-".$array['sp-obj'].".rrd";
                $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename ($filename);

                if (!file_exists ($rrd_filename)) {
                    rrdtool_create ($rrd_filename, " DS:postbits:COUNTER:600:0:U DS:bufferdrops:COUNTER:600:0:U DS:qosdrops:COUNTER:600:0:U" . $config['rrd_rra']);
                }

                // Let's print some debugging info.
                d_echo("\n\nComponent: ".$key."\n");
                d_echo("    Class-Map: ".$array['label']."\n");
                d_echo("    SPID.SPOBJ: ".$array['sp-id'].".".$array['sp-obj']."\n");
                d_echo("    PostBytes:   1.3.6.1.4.1.9.9.166.1.15.1.1.10.".$array['sp-id'].".".$array['sp-obj']." = ".$tblcbQosClassMapStats['1.3.6.1.4.1.9.9.166.1.15.1.1.10'][$array['sp-id']][$array['sp-obj']]."\n");
                d_echo("    BufferDrops: 1.3.6.1.4.1.9.9.166.1.15.1.1.21.".$array['sp-id'].".".$array['sp-obj']." = ".$tblcbQosClassMapStats['1.3.6.1.4.1.9.9.166.1.15.1.1.21'][$array['sp-id']][$array['sp-obj']]."\n");
                d_echo("    QOSDrops:    1.3.6.1.4.1.9.9.166.1.15.1.1.17.".$array['sp-id'].".".$array['sp-obj']." = ".$tblcbQosClassMapStats['1.3.6.1.4.1.9.9.166.1.15.1.1.17'][$array['sp-id']][$array['sp-obj']]."\n");

                $rrd['postbytes'] = $tblcbQosClassMapStats['1.3.6.1.4.1.9.9.166.1.15.1.1.10'][$array['sp-id']][$array['sp-obj']];
                $rrd['bufferdrops'] = $tblcbQosClassMapStats['1.3.6.1.4.1.9.9.166.1.15.1.1.21'][$array['sp-id']][$array['sp-obj']];
                $rrd['qosdrops'] = $tblcbQosClassMapStats['1.3.6.1.4.1.9.9.166.1.15.1.1.17'][$array['sp-id']][$array['sp-obj']];

                // Update rrd
                rrdtool_update ($rrd_filename, $rrd);

                // Clean-up after yourself!
                unset($filename, $rrd_filename);
            }
        } // End foreach components

    } // end if count components

    // Clean-up after yourself!
unset($type, $components, $component, $options, $module);
}
