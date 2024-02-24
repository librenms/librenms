<?php

require_once 'includes/systemd-shared.inc.php';

/**
 * Builds the graphs variable
 *
 * @param  string  $state_type
 * @param  associative-array  $systemd_mapper
 * @param  array  $state_type_ternary_depth
 * @param  array  $graphs
 * @return $graphs
 */
function systemd_graph_builder($state_type, $systemd_mapper, $state_type_ternary_depth, $graphs)
{
    $graph_name = 'systemd_' . $state_type;
    $graphs[$graph_name]['type'] = $state_type;
    if (! in_array($state_type, $state_type_ternary_depth)) {
        $graph_descr = ucfirst($state_type . ' State');
        $graphs[$graph_name]['desc'] = $graph_descr;
    } else {
        foreach ($systemd_mapper[$state_type] as $sub_state_type => $sub_state_statuses) {
            $graph_descr = ucfirst($state_type) . ' ' . ucfirst($sub_state_type) . ' State';
            $graphs[$graph_name]['sub_states'][$sub_state_type]['desc'] = $graph_descr;
        }
    }

    return $graphs;
}

/**
 * Builds a graph array and outputs the graph.
 *
 * @param  string  $state_type
 * @param  string  $app_id
 * @param  null|string  $sub_state_type
 * @param  string  $graph_desc
 */
function systemd_graph_printer($state_type, $app_id, $sub_state_type, $graph_desc)
{
    $graph_type = $state_type;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = time();
    $graph_array['id'] = $app_id;
    $graph_array['type'] = 'application_' . $state_type;
    if (! is_null($sub_state_type)) {
        $graph_array['sub_state_type'] = $sub_state_type;
    }
    echo '<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">' .
        $graph_desc .
        '</h3>
    </div>
    <div class="panel-body">
    <div class="row">';
    include 'includes/html/print-graphrow.inc.php';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'apps',
    'app' => 'systemd',
];

print_optionbar_start();

echo generate_link('All Unit States', $link_array) . ' | ';

$i = 0;
foreach ($systemd_mapper as $state_type => $state_statuses) {
    echo generate_link(ucfirst($state_type) . ' State', $link_array, ['section' => $state_type]);
    if ($i < count($systemd_mapper) - 1) {
        echo ', ';
    }
    $i++;
}

print_optionbar_end();

$graphs = [];

// Build graphs variable
if (isset($vars['section'])) {
    // Build graphs for the individual state sections (load, active, or sub).
    $graphs = systemd_graph_builder($vars['section'], $systemd_mapper, $state_type_ternary_depth, $graphs);
} else {
    // Build graphs for the combined states section (load, active, and sub).
    foreach ($systemd_mapper as $state_type => $state_status) {
        $graphs = systemd_graph_builder($state_type, $systemd_mapper, $state_type_ternary_depth, $graphs);
    }
}

// Display the built graphs
foreach ($graphs as $state_type => $values) {
    if (in_array($values['type'], $state_type_ternary_depth)) {
        foreach ($values['sub_states'] as $sub_state_type => $text) {
            systemd_graph_printer($state_type, $app['app_id'], $sub_state_type, $text['desc']);
        }
    } else {
        systemd_graph_printer($state_type, $app['app_id'], null, $values['desc']);
    }
}
