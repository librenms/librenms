<?php

$graphs = [
    'nfs-v3-stats_stats' => 'NFS v3 Statistics',
    'nfs-v3-stats_io' => 'IO',
    'nfs-v3-stats_fh' => 'File handler',
    'nfs-v3-stats_rc' => 'Reply cache',
    'nfs-v3-stats_ra' => 'Read ahead cache',
    'nfs-v3-stats_net' => 'Network stats',
    'nfs-v3-stats_rpc' => 'RPC Stats',

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
