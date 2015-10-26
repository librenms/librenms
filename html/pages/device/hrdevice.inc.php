<?php
echo '<h3>Inventory</h3>';
echo '<hr>';
echo '<table class="table table-condensed">';

// FIXME missing heading
foreach (dbFetchRows('SELECT * FROM `hrDevice` WHERE `device_id` = ? ORDER BY `hrDeviceIndex`', array($device['device_id'])) as $hrdevice) {
    echo "<tr class='list'><td>".$hrdevice['hrDeviceIndex'].'</td>';

    if ($hrdevice['hrDeviceType'] == 'hrDeviceProcessor') {
        $proc_id     = dbFetchCell("SELECT processor_id FROM processors WHERE device_id = '".$device['device_id']."' AND hrDeviceIndex = '".$hrdevice['hrDeviceIndex']."'");
        $proc_url    = 'device/device='.$device['device_id'].'/tab=health/metric=processor/';
        $proc_popup  = "onmouseover=\"return overlib('<div class=list-large>".$device['hostname'].' - '.$hrdevice['hrDeviceDescr'];
        $proc_popup .= "</div><img src=\'graph.php?id=".$proc_id.'&amp;type=processor_usage&amp;from='.$config['time']['month'].'&amp;to='.$config['time']['now']."&amp;width=400&amp;height=125\'>";
        $proc_popup .= "', RIGHT".$config['overlib_defaults'].');" onmouseout="return nd();"';
        echo "<td><a href='$proc_url' $proc_popup>".$hrdevice['hrDeviceDescr'].'</a></td>';

        $graph_array['height']      = '20';
        $graph_array['width']       = '100';
        $graph_array['to']          = $config['time']['now'];
        $graph_array['id']          = $proc_id;
        $graph_array['type']        = 'processor_usage';
        $graph_array['from']        = $config['time']['day'];
        $graph_array_zoom           = $graph_array;
        $graph_array_zoom['height'] = '150';
        $graph_array_zoom['width']  = '400';

        $mini_graph = overlib_link($proc_url, generate_lazy_graph_tag($graph_array), generate_graph_tag($graph_array_zoom),  NULL);

        echo '<td>'.$mini_graph.'</td>';
    }
    else if ($hrdevice['hrDeviceType'] == 'hrDeviceNetwork') {
        $int       = str_replace('network interface ', '', $hrdevice['hrDeviceDescr']);
        $interface = dbFetchRow('SELECT * FROM ports WHERE device_id = ? AND ifDescr = ?', array($device['device_id'], $int));
        if ($interface['ifIndex']) {
            echo '<td>'.generate_port_link($interface).'</td>';

            $graph_array['height']      = '20';
            $graph_array['width']       = '100';
            $graph_array['to']          = $config['time']['now'];
            $graph_array['id']          = $interface['port_id'];
            $graph_array['type']        = 'port_bits';
            $graph_array['from']        = $config['time']['day'];
            $graph_array_zoom           = $graph_array;
            $graph_array_zoom['height'] = '150';
            $graph_array_zoom['width']  = '400';

            // FIXME click on graph should also link to port, but can't use generate_port_link here...
            $mini_graph = overlib_link(generate_port_url($interface), generate_lazy_graph_tag($graph_array), generate_graph_tag($graph_array_zoom),  NULL);

            echo "<td>$mini_graph</td>";
        }
        else {
            echo '<td>'.stripslashes($hrdevice['hrDeviceDescr']).'</td>';
            echo '<td></td>';
        }
    }
    else {
        echo '<td>'.stripslashes($hrdevice['hrDeviceDescr']).'</td>';
        echo '<td></td>';
    }//end if

    echo '<td>'.$hrdevice['hrDeviceType'].'</td><td>'.$hrdevice['hrDeviceStatus'].'</td>';
    echo '<td>'.$hrdevice['hrDeviceErrors'].'</td><td>'.$hrdevice['hrProcessorLoad'].'</td>';
    echo '</tr>';
}//end foreach

echo '</table>';

$pagetitle[] = 'Inventory';
