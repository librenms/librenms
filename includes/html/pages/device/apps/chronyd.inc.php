<?php

$sources = get_chrony_sources($device['device_id']);

$link_array = [
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'apps',
    'app'    => 'chronyd',
];

print_optionbar_start();

echo generate_link('Tracking', $link_array);
echo ' | Sources: ';
$sources_ctr = 0;
while (isset($sources[$sources_ctr])) {
    $source = $sources[$sources_ctr];
    $label = $source;

    if ($vars['source'] == $source) {
        $label = '>>' . $source . '<<';
    }

    $sources_ctr++;

    $append = '';
    if (isset($sources[$sources_ctr])) {
        $append = ', ';
    }

    echo generate_link($label, $link_array, ['source'=>$source]) . $append;
}

print_optionbar_end();

if (! isset($vars['source'])) {
    $graphs = [
        'chronyd_time' => 'System time',
        'chronyd_frequency' => 'System clock frequency',
        'chronyd_root' => 'Root stratum',
        'chronyd_stratum' => 'Stratum level',
    ];
} else {
    $graphs = [
        'chronyd_source_sampling'   => 'Clock sampling offsets',
        'chronyd_source_frequency'  => 'Clock residual frequency',
        'chronyd_source_polling'    => 'Polling',
    ];
}

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    if (isset($vars['source'])) {
        $graph_array['source'] = $vars['source'];
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
