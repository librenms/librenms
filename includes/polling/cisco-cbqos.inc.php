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

    $MODULE = 'Cisco-CBQOS';

    require_once 'includes/component.php';
    $COMPONENT = new component();
    $options['filter']['type'] = array('=',$MODULE);
    $options['filter']['disabled'] = array('=',0);
    $options['filter']['ignore'] = array('=',0);
    $COMPONENTS = $COMPONENT->getComponents($device['device_id'],$options);

    // We only care about our device id.
    $COMPONENTS = $COMPONENTS[$device['device_id']];

    // Only collect SNMP data if we have enabled components
    if (count($COMPONENTS > 0)) {
        // Let's gather the stats..
        $tblcbQosClassMapStats = snmpwalk_array_num($device, '.1.3.6.1.4.1.9.9.166.1.15.1.1', 2);

        // Loop through the components and extract the data.
        foreach ($COMPONENTS as $KEY => $ARRAY) {
            $TYPE = $ARRAY['qos-type'];

            // Get data from the class table.
            if ($TYPE == 2) {
                // Let's make sure the RRD is setup for this class.
                $filename = "port-".$ARRAY['ifindex']."-cbqos-".$ARRAY['sp-id']."-".$ARRAY['sp-obj'].".rrd";
                $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename ($filename);

                if (!file_exists ($rrd_filename)) {
                    rrdtool_create ($rrd_filename, " DS:postbits:COUNTER:600:0:U DS:bufferdrops:COUNTER:600:0:U DS:qosdrops:COUNTER:600:0:U" . $config['rrd_rra']);
                }

                // Let's print some debugging info.
                d_echo("\n\nComponent: ".$KEY."\n");
                d_echo("    Class-Map: ".$ARRAY['label']."\n");
                d_echo("    SPID.SPOBJ: ".$ARRAY['sp-id'].".".$ARRAY['sp-obj']."\n");
                d_echo("    PostBytes:   1.3.6.1.4.1.9.9.166.1.15.1.1.10.".$ARRAY['sp-id'].".".$ARRAY['sp-obj']." = ".$tblcbQosClassMapStats['1.3.6.1.4.1.9.9.166.1.15.1.1.10'][$ARRAY['sp-id']][$ARRAY['sp-obj']]."\n");
                d_echo("    BufferDrops: 1.3.6.1.4.1.9.9.166.1.15.1.1.21.".$ARRAY['sp-id'].".".$ARRAY['sp-obj']." = ".$tblcbQosClassMapStats['1.3.6.1.4.1.9.9.166.1.15.1.1.21'][$ARRAY['sp-id']][$ARRAY['sp-obj']]."\n");
                d_echo("    QOSDrops:    1.3.6.1.4.1.9.9.166.1.15.1.1.17.".$ARRAY['sp-id'].".".$ARRAY['sp-obj']." = ".$tblcbQosClassMapStats['1.3.6.1.4.1.9.9.166.1.15.1.1.17'][$ARRAY['sp-id']][$ARRAY['sp-obj']]."\n");

                $RRD['postbytes'] = $tblcbQosClassMapStats['1.3.6.1.4.1.9.9.166.1.15.1.1.10'][$ARRAY['sp-id']][$ARRAY['sp-obj']];
                $RRD['bufferdrops'] = $tblcbQosClassMapStats['1.3.6.1.4.1.9.9.166.1.15.1.1.21'][$ARRAY['sp-id']][$ARRAY['sp-obj']];
                $RRD['qosdrops'] = $tblcbQosClassMapStats['1.3.6.1.4.1.9.9.166.1.15.1.1.17'][$ARRAY['sp-id']][$ARRAY['sp-obj']];

                // Update RRD
                rrdtool_update ($rrd_filename, $RRD);

                // Clean-up after yourself!
                unset($filename, $rrd_filename);
            }
        } // End foreach components

        echo $MODULE." ";
    } // end if count components

    // Clean-up after yourself!
    unset($TYPE, $COMPONENTS, $COMPONENT, $options, $MODULE);
}