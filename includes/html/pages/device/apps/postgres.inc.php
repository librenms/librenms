<?php

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'postgres',
];

print_optionbar_start();

echo generate_link('Total', $link_array);
echo '| DBs:';
$databases = $app->data['databases'] ?? [];
sort($databases);
foreach ($databases as $index => $db) {
    $label = $vars['database'] == $db
        ? '<span class="pagemenu-selected">' . $db . '</span>'
        : $db;

    echo generate_link($label, $link_array, ['database' => $db]);

    if ($index < (count($databases) - 1)) {
        echo ', ';
    }
}

print_optionbar_end();

$graphs = [
    'postgres_backends' => 'Backends',
    'postgres_cr' => 'Commits & Rollbacks',
    'postgres_rows' => 'Rows',
    'postgres_hr' => 'Buffer Hits & Disk Blocks Read',
    'postgres_index' => 'Indexes',
    'postgres_sequential' => 'Sequential',
];

foreach ($graphs as $key => $text) {
    $graph_type = $key;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = \LibreNMS\Config::get('time.now');
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $key;

    if (isset($vars['database'])) {
        $graph_array['database'] = $vars['database'];
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
