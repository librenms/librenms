<?php

$databases = get_postgres_databases($device['device_id']);

$link_array = [
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'apps',
    'app'    => 'postgres',
];

print_optionbar_start();

echo generate_link('Total', $link_array);
echo '| DBs:';
$db_int = 0;
while (isset($databases[$db_int])) {
    $db = $databases[$db_int];
    $label = $db;

    if ($vars['database'] == $db) {
        $label = '>>' . $db . '<<';
    }

    $db_int++;

    $append = '';
    if (isset($databases[$db_int])) {
        $append = ', ';
    }

    echo generate_link($label, $link_array, ['database'=>$db]) . $append;
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
