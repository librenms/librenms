<?php

$total = true;

if (isset($total) && $total === true) {
    $graphs = [
        'shoutcast_multi_bits'  => 'Traffic Statistics - Total of all Shoutcast servers',
        'shoutcast_multi_stats' => 'Shoutcast Statistics - Total of all Shoutcast servers',
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
}

$files = glob(Rrd::name($device['hostname'], ['app', 'shoutcast', $app['app_id']], '*.rrd'));
foreach ($files as $file) {
    $pieces = explode('-', basename($file, '.rrd'));
    $hostname = end($pieces);
    [$host, $port] = explode('_', $hostname, 2);
    $graphs = [
        'shoutcast_bits'  => 'Traffic Statistics - ' . $host . ' (Port: ' . $port . ')',
        'shoutcast_stats' => 'Shoutcast Statistics - ' . $host . ' (Port: ' . $port . ')',
    ];

    foreach ($graphs as $key => $text) {
        $graph_type = $key;
        $graph_array['height'] = '100';
        $graph_array['width'] = '215';
        $graph_array['to'] = \LibreNMS\Config::get('time.now');
        $graph_array['id'] = $app['app_id'];
        $graph_array['type'] = 'application_' . $key;
        $graph_array['hostname'] = $hostname;
        echo '<h3>' . $text . '</h3>';
        echo "<tr bgcolor='$row_colour'><td colspan=5>";

        include 'includes/html/print-graphrow.inc.php';

        echo '</td></tr>';
    }
}
