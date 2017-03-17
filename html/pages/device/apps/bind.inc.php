<?php

global $config;

$graphs = array(
    'bind_incoming' => 'Incoming',
    'bind_outgoing' => 'Outgoing',
    'bind_rr_positive' => 'RR Sets Positive',
    'bind_rr_negative' => 'RR Sets Negative',
    'bind_rtt' => 'Resolver RTT',
    'bind_resolver_failure' => 'Resolver Failures',
    'bind_resolver_qrs' => 'Resolver Quiries Sent/Received',
    'bind_resolver_naf' => 'NS Query Status',
    'bind_server_received' => 'Server Queries/Requests Received',
    'bind_server_results' => 'Server Results',
    'bind_server_issues' => 'Server Issues',
    'bind_cache_hm' => 'Cache Hits & Misses',
    'bind_cache_tree' => 'Cache Tree Memory',
    'bind_cache_heap' => 'Cache Heap Memory',
    'bind_cache_deleted' => 'Cache Record Deletion',
    'bind_adb_size' => 'Address & Name Hash Table Size',
    'bind_adb_in' => 'Address & Name In Hash Table',
    'bind_sockets_active' => 'Active Sockets',
    'bind_sockets_errors' => 'Socket Errors Per Second',
    'bind_sockets_opened' => 'Opened Sockets Per Second',
    'bind_sockets_closed' => 'Closed Sockets Per Second',
    'bind_sockets_bf' => 'Socket Bind Failures Per Second',
    'bind_sockets_cf' => 'Socket Connect Failures Per Second',
    'bind_sockets_established' => 'Connections Established Per Second',
);

foreach ($graphs as $key => $text) {
    $graph_type            = $key;
    $graph_array['height'] = '100';
    $graph_array['width']  = '215';
    $graph_array['to']     = $config['time']['now'];
    $graph_array['id']     = $app['app_id'];
    $graph_array['type']   = 'application_'.$key;

    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">'.$text.'</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/print-graphrow.inc.php';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
