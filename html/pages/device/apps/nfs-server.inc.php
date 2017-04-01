<?php

global $config;

$graphs = array(
    'nfs-server_stats_v2' => 'NFS v2 Statistics',
    'nfs-server_stats' => 'NFS v3 Statistics',
    'nfs-server_stats_v4' => 'NFS v4 Statistics',
    'nfs-server_v4ops' => 'NFS v4ops Statistics',
    'nfs-server_io' => 'IO',
    'nfs-server_fh' => 'File handler',
    'nfs-server_rc' => 'Reply cache',
    'nfs-server_ra' => 'Read ahead cache',
    'nfs-server_net' => 'Network stats',
    'nfs-server_rpc' => 'RPC Stats',

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
