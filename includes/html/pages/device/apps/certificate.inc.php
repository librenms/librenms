<?php

$domain_list = Rrd::getRrdApplicationArrays($device, $app['app_id'], 'certificate');

print_optionbar_start();

$link_array = [
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'apps',
    'app'    => 'certificate',
];

$cert_name_list = [];

foreach ($domain_list as $label) {
    $cert_name = $label;

    if ($vars['cert_name'] == $cert_name) {
        $label = sprintf('⚫ %s', $label);
    }

    array_push($cert_name_list, generate_link($label, $link_array, ['cert_name' => $cert_name]));
}

printf('%s | certificates: %s', generate_link('All Certificates', $link_array), implode(', ', $cert_name_list));

print_optionbar_end();

$graphs = [
    'certificate_age'            => 'Age',
    'certificate_remaining_days' => 'Remaining days',
];

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = time();
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    if (isset($vars['cert_name'])) {
        $graph_array['cert_name'] = $vars['cert_name'];
    }

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
