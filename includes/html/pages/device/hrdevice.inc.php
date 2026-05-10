<?php

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Models\HrDevice;
use App\Models\Port;
use App\Models\Processor;
use LibreNMS\Util\Url;

$device = DeviceCache::getPrimary();
echo '<h3>Inventory</h3>';
echo '<hr>';
echo '<table class="table table-hover">';

echo '<tr><th>Index</th><th>Description</th><th></th><th>Type</th><th>Status</th><th>Errors</th><th>Load</th></tr>';
foreach (HrDevice::whereBelongsTo($device)->orderBy('hrDeviceIndex') as $hrDevice) {
    echo "<tr><td>{$hrDevice->hrDeviceIndex}</td>";

    if ($hrDevice->hrDeviceType == 'hrDeviceProcessor') {
        $proc_id = Processor::query()
            ->whereBelongsTo($device)
            ->where('hrDeviceIndex', $hrDevice->hrDeviceIndex)
            ->value('processor_id');
        $proc_url = 'device/device=' . $device->device_id . '/tab=health/metric=processor/';
        $proc_popup = "onmouseover=\"return overlib('<div class=list-large>" . $device->hostname . ' - ' . $hrDevice->hrDeviceDescr;
        $proc_popup .= "</div><img src=\'graph.php?id=" . $proc_id . '&amp;type=processor_usage&amp;from=' . LibrenmsConfig::get('time.month') . '&amp;to=' . LibrenmsConfig::get('time.now') . "&amp;width=400&amp;height=125\'>";
        $proc_popup .= "', RIGHT" . LibrenmsConfig::get('overlib_defaults') . ');" onmouseout="return nd();"';
        echo "<td><a href='$proc_url' $proc_popup>" . $hrDevice->hrDeviceDescr . '</a></td>';

        $graph_array['height'] = '20';
        $graph_array['width'] = '100';
        $graph_array['to'] = LibrenmsConfig::get('time.now');
        $graph_array['id'] = $proc_id;
        $graph_array['type'] = 'processor_usage';
        $graph_array['from'] = LibrenmsConfig::get('time.day');
        $graph_array_zoom = $graph_array;
        $graph_array_zoom['height'] = '150';
        $graph_array_zoom['width'] = '400';

        $mini_graph = Url::overlibLink($proc_url, Url::lazyGraphTag($graph_array), Url::graphTag($graph_array_zoom));

        echo '<td>' . $mini_graph . '</td>';
    } elseif ($hrDevice->hrDeviceType == 'hrDeviceNetwork') {
        $int = str_replace('network interface ', '', $hrDevice->hrDeviceDescr);
        $port = Port::query()
            ->whereBelongsTo($device)
            ->where(fn ($q) => $q->query()
                ->orWhere('ifDescr', $int)
                ->orWhere('ifName', $int))
            ->first();
        if ($port->ifIndex) {
            if (! empty($port->port_descr_type)) {
                $port_text = $port->port_descr_type . ' (' . $int . ')';
            } else {
                $port_text = $int;
            }
            echo '<td>' . Url::portLink($port, $port_text) . '</td>';

            $graph_array['height'] = '20';
            $graph_array['width'] = '100';
            $graph_array['to'] = LibrenmsConfig::get('time.now');
            $graph_array['id'] = $port->port_id;
            $graph_array['type'] = 'port_bits';
            $graph_array['from'] = LibrenmsConfig::get('time.day');
            $graph_array_zoom = $graph_array;
            $graph_array_zoom['height'] = '150';
            $graph_array_zoom['width'] = '400';

            $mini_graph = Url::overlibLink(Url::portUrl($port), Url::lazyGraphTag($graph_array), Url::graphTag($graph_array_zoom));

            echo "<td>$mini_graph</td>";
        } else {
            echo '<td>' . stripslashes((string) $hrdevice['hrDeviceDescr']) . '</td>';
            echo '<td></td>';
        }
    } else {
        echo '<td>' . stripslashes((string) $hrdevice['hrDeviceDescr']) . '</td>';
        echo '<td></td>';
    }//end if

    echo '<td>' . $hrdevice['hrDeviceType'] . '</td><td>' . $hrdevice['hrDeviceStatus'] . '</td>';
    echo '<td>' . $hrdevice['hrDeviceErrors'] . '</td><td>' . $hrdevice['hrProcessorLoad'] . '</td>';
    echo '</tr>';
}//end foreach

echo '</table>';

$pagetitle[] = 'Inventory';
