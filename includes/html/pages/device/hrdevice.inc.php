<?php

echo '<h3>Inventory</h3>';
echo '<hr>';
echo '<table class="table table-condensed">';

echo "<tr class='list'><th>Index</th><th>Description</th><th></th><th>Type</th><th>Status</th><th>Errors</th><th>Load</th></tr>";
foreach (dbFetchRows('SELECT * FROM `hrDevice` WHERE `device_id` = ? ORDER BY `hrDeviceIndex`', [$device['device_id']]) as $hrdevice) {
    echo "<tr class='list'><td>" . $hrdevice['hrDeviceIndex'] . '</td>';

    if ($hrdevice['hrDeviceType'] == 'hrDeviceProcessor') {
        $proc_id = dbFetchCell("SELECT processor_id FROM processors WHERE device_id = '" . $device['device_id'] . "' AND hrDeviceIndex = '" . $hrdevice['hrDeviceIndex'] . "'");
        $proc_url = 'device/device=' . $device['device_id'] . '/tab=health/metric=processor/';
        $proc_popup = "onmouseover=\"return overlib('<div class=list-large>" . $device['hostname'] . ' - ' . $hrdevice['hrDeviceDescr'];
        $proc_popup .= "</div><img src=\'graph.php?id=" . $proc_id . '&amp;type=processor_usage&amp;from=' . \LibreNMS\Config::get('time.month') . '&amp;to=' . \LibreNMS\Config::get('time.now') . "&amp;width=400&amp;height=125\'>";
        $proc_popup .= "', RIGHT" . \LibreNMS\Config::get('overlib_defaults') . ');" onmouseout="return nd();"';
        echo "<td><a href='$proc_url' $proc_popup>" . $hrdevice['hrDeviceDescr'] . '</a></td>';

        $graph_array['height'] = '20';
        $graph_array['width'] = '100';
        $graph_array['to'] = \LibreNMS\Config::get('time.now');
        $graph_array['id'] = $proc_id;
        $graph_array['type'] = 'processor_usage';
        $graph_array['from'] = \LibreNMS\Config::get('time.day');
        $graph_array_zoom = $graph_array;
        $graph_array_zoom['height'] = '150';
        $graph_array_zoom['width'] = '400';

        $mini_graph = \LibreNMS\Util\Url::overlibLink($proc_url, \LibreNMS\Util\Url::lazyGraphTag($graph_array), \LibreNMS\Util\Url::graphTag($graph_array_zoom));

        echo '<td>' . $mini_graph . '</td>';
    } elseif ($hrdevice['hrDeviceType'] == 'hrDeviceNetwork') {
        $int = str_replace('network interface ', '', $hrdevice['hrDeviceDescr']);
        $interface = dbFetchRow('SELECT * FROM ports WHERE device_id = ? AND (ifDescr = ? or ifName = ?)', [$device['device_id'], $int, $int]);
        $interface = cleanPort($interface);
        if ($interface['ifIndex']) {
            if (! empty($interface['port_descr_type'])) {
                $interface_text = $interface['port_descr_type'] . ' (' . $int . ')';
            } else {
                $interface_text = $int;
            }
            echo '<td>' . generate_port_link($interface, $interface_text) . '</td>';

            $graph_array['height'] = '20';
            $graph_array['width'] = '100';
            $graph_array['to'] = \LibreNMS\Config::get('time.now');
            $graph_array['id'] = $interface['port_id'];
            $graph_array['type'] = 'port_bits';
            $graph_array['from'] = \LibreNMS\Config::get('time.day');
            $graph_array_zoom = $graph_array;
            $graph_array_zoom['height'] = '150';
            $graph_array_zoom['width'] = '400';

            $mini_graph = \LibreNMS\Util\Url::overlibLink(generate_port_url($interface), \LibreNMS\Util\Url::lazyGraphTag($graph_array), \LibreNMS\Util\Url::graphTag($graph_array_zoom));

            echo "<td>$mini_graph</td>";
        } else {
            echo '<td>' . stripslashes($hrdevice['hrDeviceDescr']) . '</td>';
            echo '<td></td>';
        }
    } else {
        echo '<td>' . stripslashes($hrdevice['hrDeviceDescr']) . '</td>';
        echo '<td></td>';
    }//end if

    echo '<td>' . $hrdevice['hrDeviceType'] . '</td><td>' . $hrdevice['hrDeviceStatus'] . '</td>';
    echo '<td>' . $hrdevice['hrDeviceErrors'] . '</td><td>' . $hrdevice['hrProcessorLoad'] . '</td>';
    echo '</tr>';
}//end foreach

echo '</table>';

$pagetitle[] = 'Inventory';
