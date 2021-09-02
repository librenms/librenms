<?php

$asterisk_graphs = [
    'asterisk_calls' => 'Asterisk - Calls',
    'asterisk_channels' => 'Asterisk - Channels',
    'asterisk_sip' => 'Asterisk - SIP Peers',
    'asterisk_iax2' => 'Asterisk - IAX2 Peers',
];

foreach ($asterisk_graphs as $asterisk_graphs_key => $asterisk_graphs_value) {
    $graph_type = $asterisk_graphs_key;
    $graph_array['height'] = '100';
    $graph_array['id'] = $app['app_id'];
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['type'] = 'application_' . $asterisk_graphs_key;
    $graph_array['width'] = '215';

    echo '<div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">' . $asterisk_graphs_value . '</h3></div>
        <div class="panel-body"><div class="row">';
    include 'includes/html/print-graphrow.inc.php';
    echo '</div></div></div></div>';
}
