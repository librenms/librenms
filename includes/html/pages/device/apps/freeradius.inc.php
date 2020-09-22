<?php

$graphs = [
    'freeradius_access'       => 'FreeRADIUS - Access',
    'freeradius_auth'         => 'FreeRADIUS - Auth',
    'freeradius_acct'         => 'FreeRADIUS - Acct',
    'freeradius_proxy_access' => 'FreeRADIUS - Proxy Access',
    'freeradius_proxy_auth'   => 'FreeRADIUS - Proxy Auth',
    'freeradius_proxy_acct'   => 'FreeRADIUS - Proxy Acct',
    'freeradius_queue'        => 'FreeRADIUS - Queue',
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
