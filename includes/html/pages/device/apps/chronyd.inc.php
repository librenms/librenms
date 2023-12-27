<?php

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'chronyd',
];

print_optionbar_start();

echo generate_link('Tracking', $link_array);
echo ' | Sources: ';
$sources = $app->data['sources'] ?? [];
sort($sources);
foreach ($sources as $index => $source) {
    $label = $vars['source'] == $source
        ? '<span class="pagemenu-selected">' . $source . '</span>'
        : $source;

    echo generate_link($label, $link_array, ['source' => $source]);

    if ($index < (count($sources) - 1)) {
        echo ', ';
    }
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
        'chronyd_source_sampling' => 'Clock sampling offsets',
        'chronyd_source_frequency' => 'Clock residual frequency',
        'chronyd_source_polling' => 'Polling',
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
