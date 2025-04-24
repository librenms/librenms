<?php

$name = 'poudriere';

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'poudriere',
];

if (isset($vars['poudriere_set'])) {
    $vars['poudriere_set'] = htmlspecialchars($vars['poudriere_set']);
}

$app_data = $app->data;

print_optionbar_start();

// print the link to the totals
$label = (isset($vars['poudriere_page']) || isset($vars['poudriere_set']))
    ? 'Totals'
    : '<span class="pagemenu-selected">Totals</span>';
echo generate_link($label, $link_array);
echo ' | ';

// print the link to the details page
$label = (! isset($vars['poudriere_page']) && $vars['poudriere_page'] != 'details')
    ? 'Details'
    : '<span class="pagemenu-selected">Details</span>';
echo generate_link($label, $link_array, ['poudriere_page' => 'details']);
echo ' | Sets: ';

$index_int = 0;
foreach ($app_data['sets'] as $index => $set_name) {
    $set_name = htmlspecialchars($set_name);
    $label = (! isset($vars['poudriere_set']) || $vars['poudriere_set'] != $set_name)
        ? $set_name
        : '<span class="pagemenu-selected">' . $set_name . '</span>';
    $index_int++;
    echo generate_link($label, $link_array, ['poudriere_set' => $set_name]);
    if (isset($app_data['sets'][$index_int])) {
        echo ', ';
    }
}

print_optionbar_end();

$graphs = [];
if (isset($vars['poudriere_page']) && $vars['poudriere_page'] == 'details') {
    print_optionbar_start();
    if (isset($app_data['status']) && ! is_null($app_data['status'])) {
        echo "<b><center>Status</center></b><br>\n";
        $table = [
            'headers' => [
                'Set',
                'Ports',
                'Jail',
                'Build',
                'Status',
                'Queue',
                'Built',
                'Fail',
                'Skip',
                'Ignore',
                'Fetch',
                'Remain',
                'Time',
                'Logs',
            ],
            'rows' => [],
        ];
        $status_split = explode("\n", $app_data['status']);
        $status_split_int = 1;
        while (isset($status_split[$status_split_int])) {
            $line = preg_replace("/^\s+/", '', $status_split[$status_split_int]);
            $row = preg_split("/\s+/", $line, 14);
            if (isset($row[13])) {
                $table['rows'][] = [
                    ['data' => $row[0]],
                    ['data' => $row[1]],
                    ['data' => $row[2]],
                    ['data' => $row[3]],
                    ['data' => $row[4]],
                    ['data' => $row[5]],
                    ['data' => $row[6]],
                    ['data' => $row[7]],
                    ['data' => $row[8]],
                    ['data' => $row[9]],
                    ['data' => $row[10]],
                    ['data' => $row[11]],
                    ['data' => $row[12]],
                    ['data' => $row[13]],
                ];
            }
            $status_split_int++;
        }
        echo view('widgets/sortable_table', $table);
    }
    if (isset($app_data['build_info']) && ! is_null($app_data['build_info'])) {
        echo "<b><center>Build Info</center></b><br>\n";
        $table = [
            'headers' => [
                'Set',
                'Build',
                'ID',
                'Total',
                'Origin',
                'Pkg Name',
                'Phase',
                'Time',
                'TMP FS',
                'CPU%',
                'MEM%',
            ],
            'rows' => [],
        ];
        $build_split = explode("\n", $app_data['build_info']);
        $build_split_int = 0;
        while (isset($build_split[$build_split_int])) {
            $line = preg_replace("/^\s+/", '', $build_split[$build_split_int]);
            if (preg_match('/\[.*\]\ +\[.*\]\ +\[.*\]/', $line)) {
                $row = preg_split("/\s+/", $line, 14);
                if (isset($row[0])) {
                    $current_set = preg_replace("/[\[\]]/", '', $row[0]);
                } else {
                    $current_set = '';
                }
                if (isset($row[1])) {
                    $current_build = preg_replace("/[\[\]]/", '', $row[1]);
                } else {
                    $current_build = '';
                }
            } elseif (preg_match('/^\[.*\]/', $line)) {
                $line_split = preg_split("/[^\w\d\-\,\.\:\/\@\%]+/", $line, 14);
                if (isset($line_split[9])) {
                    $tmp_fs = $line_split[7];
                    $cpu_perc = $line_split[8];
                    $mem_perc = $line_split[9];
                } else {
                    $tmp_fs = '-';
                    $cpu_perc = $line_split[7];
                    $mem_perc = $line_split[8];
                }
                $table['rows'][] = [
                    ['data' => $current_set],
                    ['data' => $current_build],
                    ['data' => $line_split[1]],
                    ['data' => $line_split[2]],
                    ['data' => $line_split[3]],
                    ['data' => $line_split[4]],
                    ['data' => $line_split[5]],
                    ['data' => $line_split[6]],
                    ['data' => $tmp_fs],
                    ['data' => $cpu_perc],
                    ['data' => $mem_perc],
                ];
            }

            $build_split_int++;
        }
        echo view('widgets/sortable_table', $table);
    }
    if (isset($app_data['history']) && ! is_null($app_data['history'])) {
        echo "<b><center>History</center></b><br>\n";
        $table = [
            'headers' => [
                'Set',
                'Ports',
                'Jail',
                'Build',
                'Status',
                'Queue',
                'Built',
                'Fail',
                'Skip',
                'Ignore',
                'Fetch',
                'Remain',
                'Time',
                'Logs',
            ],
            'rows' => [],
        ];
        $status_split = explode("\n", $app_data['history']);
        $status_split_int = 1;
        while (isset($status_split[$status_split_int])) {
            $line = preg_replace("/^\s+/", '', $status_split[$status_split_int]);
            $row = preg_split("/\s+/", $line, 14);
            if (isset($row[13])) {
                $table['rows'][] = [
                    ['data' => $row[0]],
                    ['data' => $row[1]],
                    ['data' => $row[2]],
                    ['data' => $row[3]],
                    ['data' => $row[4]],
                    ['data' => $row[5]],
                    ['data' => $row[6]],
                    ['data' => $row[7]],
                    ['data' => $row[8]],
                    ['data' => $row[9]],
                    ['data' => $row[10]],
                    ['data' => $row[11]],
                    ['data' => $row[12]],
                    ['data' => $row[13]],
                ];
            }
            $status_split_int++;
        }
        echo view('widgets/sortable_table', $table);
    }
    print_optionbar_end();
} else {
    $graphs = [
        [
            'type' => 'status',
            'description' => 'General Status',
        ],
        [
            'type' => 'phase',
            'description' => 'Build Phase',
        ],
        [
            'type' => 'time',
            'description' => 'Build Time',
        ],
        [
            'type' => 'log_size',
            'description' => 'Log Size',
        ],
        [
            'type' => 'package_size',
            'description' => 'Package Size',
        ],
        [
            'type' => 'cpu_perc',
            'description' => 'CPU%',
        ],
        [
            'type' => 'mem_perc',
            'description' => 'Memory%',
        ],
        [
            'type' => 'time_comparison',
            'description' => 'Time Comparison(CPU, User, System)',
        ],
        [
            'type' => 'cpu_time',
            'description' => 'Time, CPU',
        ],
        [
            'type' => 'user_time',
            'description' => 'Time, User',
        ],
        [
            'type' => 'system_time',
            'description' => 'Time, System',
        ],
        [
            'type' => 'rss',
            'description' => 'RSS',
        ],
        [
            'type' => 'threads',
            'description' => 'Threads',
        ],
        [
            'type' => 'major_faults',
            'description' => 'Faults, Major',
        ],
        [
            'type' => 'minor_faults',
            'description' => 'Faults, Minor',
        ],
        [
            'type' => 'swaps',
            'description' => 'Swaps',
        ],
        [
            'type' => 'size_comparison',
            'description' => 'Size, Comparison(Stack, Data, Text)',
        ],
        [
            'type' => 'stack_size',
            'description' => 'Size, Stack',
        ],
        [
            'type' => 'data_size',
            'description' => 'Size, Data',
        ],
        [
            'type' => 'text_size',
            'description' => 'Size, Text',
        ],
        [
            'type' => 'read_blocks',
            'description' => 'Read Blocks',
        ],
        [
            'type' => 'copy_on_write_faults',
            'description' => 'COW Faults',
        ],
        [
            'type' => 'context_switches_comparison',
            'description' => 'Context Switches Comparison(Voluntary, Involuntary)',
        ],
        [
            'type' => 'voluntary_context_switches',
            'description' => 'Context Switches, Voluntary',
        ],
        [
            'type' => 'involuntary_context_switches',
            'description' => 'Context Switches, Involuntary',
        ],
    ];
}

foreach ($graphs as $key => $graph_info) {
    $graph_type = $graph_info['type'];
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = time();
    $graph_array['id'] = $app['app_id'];
    $graph_array['type'] = 'application_' . $name . '_' . $graph_info['type'];
    if (isset($vars['poudriere_set'])) {
        $graph_array['poudriere_set'] = $vars['poudriere_set'];
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
