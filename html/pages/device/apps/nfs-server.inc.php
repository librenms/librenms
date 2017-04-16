<?php

global $config;

// stat => array(text, rrd)
$graphs = array(
    'nfs-server_net' => array('Network stats', 'default'),
    'nfs-server_rpc' => array('RPC Stats', 'default'),
    'nfs-server_stats' => array('NFS v3 Statistics', 'proc3'),
    'nfs-server_v4ops' => array('NFS v4ops Statistics', 'proc4ops'),
    'nfs-server_io' => array('IO', 'default'),
    'nfs-server_fh' => array('File handler', 'default') ,
    'nfs-server_rc' => array('Reply cache', 'default'),
    'nfs-server_ra' => array('Read ahead cache', 'default'),
    'nfs-server_stats_v4' => array('NFS v4 Statistics', 'proc4'),
    'nfs-server_stats_v2' => array('NFS v2 Statistics', 'proc2'),
);

foreach ($graphs as $key => $info) {
   // check if they exist
    if (!rrdtool_check_rrd_exists(rrd_name($device['hostname'], 'app-nfs-server-'. $info[1] . '-'. $app['app_id']))) {
        continue;
    }
    
    $graph_type            = $key;
    $graph_array['height'] = '100';
    $graph_array['width']  = '215';
    $graph_array['to']     = $config['time']['now'];
    $graph_array['id']     = $app['app_id'];
    $graph_array['type']   = 'application_'.$key;

    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">' . $info[0] . '</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/print-graphrow.inc.php';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
