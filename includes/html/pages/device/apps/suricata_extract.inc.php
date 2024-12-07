<?php

$graphs = [
    'suricata_extract_sub' => 'Submission',
    'suricata_extract_ignored_host' => 'Ignored By Host',
    'suricata_extract_ignored_ip' => 'Ignored By IP',
    'suricata_extract_ignored_ip_src' => 'Ignored By IP Source',
    'suricata_extract_ignored_ip_dest' => 'Ignored By IP Destination',
    'suricata_extract_sub_fail' => 'Submission Failure',
    'suricata_extract_errors' => 'Errors',
    'suricata_extract_truncated' => 'File Truncated',
    'suricata_extract_zero_sized' => 'File Zero Sized',
    'suricata_extract_sub_size' => 'Total Size Of Submissions',
    'suricata_extract_sub_codes' => 'HTTP Submission Result Codes',
    'suricata_extract_sub_2xx' => 'HTTP Submission Result Code, 2xx',
    'suricata_extract_sub_3xx' => 'HTTP Submission Result Code, 3xx',
    'suricata_extract_sub_4xx' => 'HTTP Submission Result Code, 4xx',
    'suricata_extract_sub_5xx' => 'HTTP Submission Result Code, 5xx',
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
