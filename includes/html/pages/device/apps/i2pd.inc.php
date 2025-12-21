<?php

/*

LibreNMS Application for I2PD
Application page

@author     Kossusukka <kossusukka@kossulab.net>

LICENSE - GPLv3

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
version 3. See https://www.gnu.org/licenses/gpl-3.0.txt

*/

$graphs = [
    'i2pd_uptime' => 'I2PD - Uptime',
    'i2pd_total_bytes' => 'I2PD - Data transferred',
    'i2pd_bw_1s' => 'I2PD - Bandwidth (1s avg)',
    'i2pd_bw_15s' => 'I2PD - Bandwidth (15s avg)',
    'i2pd_net_status' => 'I2PD - Network status code',
    'i2pd_tunnels_participating' => 'I2PD - Tunnels active',
    'i2pd_tunnels_successrate' => 'I2PD - Tunnel success rate',
    'i2pd_peers' => 'I2PD - Peers known/active',
];

print_optionbar_start();
if (! isset($app->data['net_stat_code'])) {
    // no data?
    $netstatus = '<div style="display: inline-block; font-style: italic;">NO DATA</div>';
} elseif ($app->data['net_stat_code'] >= 8) {
    // Network status critical or failed
    $netstatus = '<div style="display: inline-block; color: red;">ERROR: ' . $app->data['net_stat_msg'] . ' (' . $app->data['net_stat_code'] . ')</div>';
} elseif ($app->data['net_stat_code'] > 1) {
    // Network status degraded
    $netstatus = '<div style="display: inline-block; color: yellow;">WARN: ' . $app->data['net_stat_msg'] . ' (' . $app->data['net_stat_code'] . ')</div>';
} else {
    // Network works perfectly
    $netstatus = '<div style="display: inline-block; color: green;">' . $app->data['net_stat_msg'] . ' (' . $app->data['net_stat_code'] . ')</div>';
}
echo '<h4>Network status: ' . $netstatus . '</h4>';
print_optionbar_end();

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \App\Facades\LibrenmsConfig::get('time.now');
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">' . $text . '</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/html/print-graphrow.inc.php';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
