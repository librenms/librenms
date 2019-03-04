<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       http://librenms.org
 * @copyright  2019 LibreNMS
 * @author     Mark Schouten <mark@tuxis.nl>
*/

include('includes/application/proxmox.inc.php');

if (!isset($config['enable_proxmox']) || !$config['enable_proxmox']) {
    print_error('Proxmox agent was discovered on this host. Please enable Proxmox in your config.');
} else {
    $graphs = [
        'proxmox_traffic' => 'Traffic',
    ];

    foreach (proxmox_node_vms(var_get('device')) as $nvm) {
        $vm = proxmox_vm_info($nvm['vmid'], $nvm['cluster']);

        foreach ($vm['ports'] as $port) {
            foreach ($graphs as $key => $text) {
                $graph_type = 'proxmox_traffic';

                $graph_array['height'] = '100';
                $graph_array['width'] = '215';
                $graph_array['to'] = $config['time']['now'];
                $graph_array['id'] = $vm['app_id'];
                $graph_array['device_id'] = $vm['device_id'];
                $graph_array['type'] = 'application_' . $key;
                $graph_array['port'] = $port['port'];
                $graph_array['vmid'] = $vm['vmid'];
                $graph_array['cluster'] = $vm['cluster'];
                $graph_array['hostname'] = $vm['description'];

                echo '<h3>' . $text . ' ' . $port['port'] . '@' . $vm['description'] . '</h3>';

                echo "<tr bgcolor='$row_colour'><td colspan=5>";

                include 'includes/print-graphrow.inc.php';

                echo '</td></tr>';
            }
        }
    }
}
