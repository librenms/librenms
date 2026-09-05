<?php

$strongswan_tunnels = Rrd::getRrdApplicationArrays($device, $app['app_id'], 'strongswan');
$strongswan_labels = $app['data']['labels'] ?? [];

print_optionbar_start();

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'strongswan',
];

$tunnel_list = [];
foreach ($strongswan_tunnels as $tunnel) {
    if ($tunnel === 'global') {
        continue;
    }

    $label = htmlspecialchars((string) ($strongswan_labels[$tunnel] ?? $tunnel));

    if (isset($vars['tunnel']) && ($vars['tunnel'] == $tunnel)) {
        $label = '<span class="pagemenu-selected">' . $label . '</span>';
    }

    $tunnel_list[] = generate_link($label, $link_array, ['tunnel' => $tunnel]);
}

printf('%s | tunnels: %s', generate_link('All tunnels', $link_array), implode(', ', $tunnel_list));

print_optionbar_end();

$selected_label = isset($vars['tunnel'])
    ? htmlspecialchars((string) ($strongswan_labels[$vars['tunnel']] ?? $vars['tunnel']))
    : 'all tunnels';

// Per-tunnel / overview graphs (respect the selected tunnel via $vars['tunnel'])
$graphs = [
    'strongswan_bits_in' => 'Inbound traffic (bits/s)',
    'strongswan_bits_out' => 'Outbound traffic (bits/s)',
    'strongswan_packets_in' => 'Inbound packets/s',
    'strongswan_packets_out' => 'Outbound packets/s',
    'strongswan_state' => 'Tunnel state (1=established)',
    'strongswan_children' => 'Installed child SAs',
    'strongswan_reestablishes' => 'Re-establishments/s',
];

foreach ($graphs as $key => $text) {
    $graph_array = [];
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = time();
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    if (isset($vars['tunnel'])) {
        $graph_array['tunnel'] = $vars['tunnel'];
    }

    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">' . $text . ' — ' . $selected_label . '</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/html/print-graphrow.inc.php';
    echo '</div></div></div>';
}

// Global daemon counters (not per-tunnel)
$global_graphs = [
    'strongswan_global_rekeys' => 'Global rekeys/s',
    'strongswan_global_errors' => 'Global errors/s (invalid / invalid SPI)',
];

foreach ($global_graphs as $key => $text) {
    $graph_array = [];
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = time();
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">' . $text . '</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/html/print-graphrow.inc.php';
    echo '</div></div></div>';
}
