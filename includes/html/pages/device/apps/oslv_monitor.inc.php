<?php

$name = 'oslv_monitor';

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'oslv_monitor',
];

$app_data = $app->data;

print_optionbar_start();

$label = isset($vars['oslvm'])
    ? 'Totals'
    : '<span class="pagemenu-selected">Totals</span>';
echo generate_link($label, $link_array);

print_optionbar_end();

if (isset($app_data['backend']) && $app_data['backend'] == 'FreeBSD') {
    $graphs = [
        [
            'type' => 'cpu_percent',
            'description' => 'CPU Usage Percent',
        ],
        [
            'type' => 'mem_percent',
            'description' => 'Memory Usage Percent',
        ],
        [
            'type' => 'time',
            'description' => 'CPU/System Time in secs/sec',
        ],
        [
            'type' => 'procs',
            'description' => 'Processes',
        ],
        [
            'type' => 'blocks',
            'description' => 'Blocks, Read/Write',
        ],
        [
            'type' => 'cows',
            'description' => 'Copy-on-Write Faults',
        ],
        [
            'type' => 'sizes',
            'description' => 'Data, Stack, Text in Kbytes',
        ],
        [
            'type' => 'rss',
            'description' => 'Real Memory(Resident Set Size) in Kbytes',
        ],
        [
            'type' => 'vsz',
            'description' => 'Virtual Size in Kbytes',
        ],
        [
            'type' => 'messages',
            'description' => 'Messages, Sent/Received',
        ],
        [
            'type' => 'faults',
            'description' => 'Faults, Major/Minor',
        ],
        [
            'type' => 'switches',
            'description' => 'Context Switches, (In)Voluntary',
        ],
        [
            'type' => 'swaps',
            'description' => 'Swaps',
        ],
        [
            'type' => 'signals_taken',
            'description' => 'Signals Taken',
        ],
        [
            'type' => 'etime',
            'description' => 'Elapsed Time in seconds',
        ],
    ];
}

foreach ($graphs as $key => $graph_info) {
    $graph_type = $graph_info['type'];
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = time();
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $name . '_' . $app_data['backend'] . '_' . $graph_info['type'];
    if (isset($vars['oslvm'])) {
        $graph_array['oslvm'] = $vars['oslvm'];
    }

    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">' . $graph_info['description'] . '</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/html/print-graphrow.inc.php';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
