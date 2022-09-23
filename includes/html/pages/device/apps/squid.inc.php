<?php

$graphs = [
    'squid_bytehit' => 'Byte Hits',
    'squid_reqhit' => 'Request Hits',
    'squid_http' => 'Client HTTP',
    'squid_httpbw' => 'Client HTTP Bandwidth',
    'squid_server' => 'Server HTTP',
    'squid_serverbw' => 'Server HTTP Bandwidth',
    'squid_clients' => 'Clients',
    'squid_cputime' => 'CPU Time',
    'squid_cpuusage' => 'CPU Usage',
    'squid_filedescr' => 'File Descriptors',
    'squid_memory' => 'Memory',
    'squid_objcount' => 'Object Count',
    'squid_pagefaults' => 'Pagefaults',
    'squid_sysnumread' => 'Sys Read',
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
