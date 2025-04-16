<?php

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'sneck',
];

$sneck_data = $app->app_id;
if (isset($sneck_data)) {
    $checks = $app->data['checks'] ?? [];
    $checks_list = array_keys($checks);
    $debugs = $app->data['debugs'] ?? [];
    $debugs_list = array_keys($debugs);
    if (isset($checks_list[0]) || isset($debugs_list[0])) {
        print_optionbar_start();
        echo generate_link('Overview', $link_array) . "<br>\n";
        if (isset($checks_list[0])) {
            echo 'Check Info: ';
            foreach ($checks_list as $index => $check) {
                $label = $vars['sneck_check'] == $check
                    ? '<span class="pagemenu-selected">' . htmlspecialchars($check) . '</span>'
                    : htmlspecialchars($check);

                echo generate_link($label, $link_array, ['sneck_check' => $check]) . "\n";

                if ($index < (count($checks_list) - 1)) {
                    echo ', ';
                } else {
                    echo "<br>\n";
                }
            }
        }
        if (isset($debugs_list[0])) {
            echo 'Debug Info: ';
            foreach ($debugs_list as $index => $debug) {
                $label = $vars['sneck_debug'] == $debug
                    ? '<span class="pagemenu-selected">' . htmlspecialchars($debug) . '</span>'
                    : htmlspecialchars($debug);

                echo generate_link($label, $link_array, ['sneck_debug' => $debug]) . "\n";

                if ($index < (count($debugs_list) - 1)) {
                    echo ', ';
                } else {
                    echo "<br>\n";
                }
            }
        }
        print_optionbar_end();
    }
}

if ((isset($vars['sneck_check']) && isset($app->data['checks'][$vars['sneck_check']])) || (isset($vars['sneck_debug']) && isset($app->data['debugs'][$vars['sneck_debug']]))) {
    $type = 'checks';
    $type_name = '';
    if (isset($vars['sneck_debug'])) {
        $type = 'debugs';
        $type_name = $vars['sneck_debug'];
    } else {
        $type_name = $vars['sneck_check'];
    }
    print_optionbar_start();
    // is the template used
    if (isset($app->data[$type][$type_name]['check'])) {
        echo '<b>Check:</b> ' . htmlspecialchars($app->data[$type][$type_name]['check']) . "<br>\n";
    }
    // what was ran post templating
    if (isset($app->data[$type][$type_name]['ran'])) {
        echo '<b>Ran:</b> ' . htmlspecialchars($app->data[$type][$type_name]['ran']) . "<br>\n";
    }
    // exit code
    if (isset($app->data[$type][$type_name]['exit'])) {
        echo '<b>Exit:</b> ' . htmlspecialchars($app->data[$type][$type_name]['exit']) . "<br>\n";
    }
    // error non-standard exit info
    if (isset($app->data[$type][$type_name]['error'])) {
        echo '<b>Error:</b> ' . htmlspecialchars($app->data[$type][$type_name]['error']) . "<br>\n";
    }
    // output
    if (isset($app->data[$type][$type_name]['output'])) {
        echo "<b>Output...</b><br>\n<pre>";
        echo htmlspecialchars($app->data[$type][$type_name]['output']) . "\n";
        echo "</pre><br>\n";
    }
    echo "<b>Raw JSON:</b><br>\n";
    echo "<pre>\n" . htmlspecialchars(json_encode($app->data[$type][$type_name], JSON_PRETTY_PRINT)) . "</pre>\n";
    print_optionbar_end();
} else {
    $graphs = [
        'sneck_results' => 'Results',
        'sneck_time' => 'Time Difference',
    ];

    foreach ($graphs as $key => $text) {
        $graph_type = $key;
        $graph_array['height'] = '100';
        $graph_array['width'] = '215';
        $graph_array['to'] = \LibreNMS\Config::get('time.now');
        $graph_array['id'] = $app->app_id;
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

    // print returned all info from sneck with alert info broken out
    if (isset($sneck_data)) {
        print_optionbar_start();
        echo 'Last Return...<br>';
        echo "<b>Alert(s):</b><br>\n";
        echo str_replace("\n", "<br>\n", htmlspecialchars($app->data['alertString'])) . "<br><br>\n";
        echo "<b>Raw JSON:</b><br>\n";
        echo "<pre>\n" . htmlspecialchars(json_encode($app->data, JSON_PRETTY_PRINT)) . "</pre>\n";
        print_optionbar_end();
    }
}
