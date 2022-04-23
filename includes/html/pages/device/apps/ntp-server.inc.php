<?php

$graphs = [
    'ntp-server_stats'   => 'NTPD Server - Statistics',
    'ntp-server_freq'    => 'NTPD Server - Frequency',
    'ntp-server_stratum' => 'NTPD Server - Stratum',
    'ntp-server_buffer'  => 'NTPD Server - Buffer',
    'ntp-server_bits'    => 'NTPD Server - Packets Sent/Received',
    'ntp-server_packets' => 'NTPD Server - Packets Dropped/Ignored',
    'ntp-server_uptime'  => 'NTPD Server - Uptime',
];

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
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
