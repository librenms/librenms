<?php

$vm = proxmox_vm_info(var_get('vmid'), var_get('instance'));

$graphs = [
    'proxmox_traffic'       => 'Traffic',
];

foreach ($vm['ports'] as $port) {
    foreach ($graphs as $key => $text) {
        $graph_type = 'proxmox_traffic';

        $graph_array['height'] = '100';
        $graph_array['width'] = '215';
        $graph_array['to'] = \LibreNMS\Config::get('time.now');
        $graph_array['id'] = $vm['app_id'];
        $graph_array['device_id'] = $vm['device_id'];
        $graph_array['type'] = 'application_' . $key;
        $graph_array['port'] = $port['port'];
        $graph_array['vmid'] = $vm['vmid'];
        $graph_array['cluster'] = $vm['cluster'];
        $graph_array['hostname'] = $vm['description'];

        echo '<h3>' . $text . ' ' . $port['port'] . '@' . $vm['description'] . '</h3>';

        echo "<tr bgcolor='$row_colour'><td colspan=5>";

        include 'includes/html/print-graphrow.inc.php';

        echo '</td></tr>';
    }
}
