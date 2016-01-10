<?php

// Graphs are printed in the order they exist in $config['graph_types']
$link_array = array(
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'graphs',
);

$bg = '#ffffff';

echo '<div style="clear: both;">';

print_optionbar_start();

echo "<span style='font-weight: bold;'>Graphs</span> &#187; ";

foreach (dbFetchRows('SELECT * FROM device_graphs WHERE device_id = ? ORDER BY graph', array($device['device_id'])) as $graph) {
    $section = $config['graph_types']['device'][$graph['graph']]['section'];
    if ($section != '') {
        $graph_enable[$section][$graph['graph']] = $graph['graph'];
    }
}

enable_graphs($device, $graph_enable);

$sep = '';
foreach ($graph_enable as $section => $nothing) {
    if (isset($graph_enable) && is_array($graph_enable[$section])) {
        $type = strtolower($section);
        if (!$vars['group']) {
            $vars['group'] = $type;
        }

        echo $sep;
        if ($vars['group'] == $type) {
            echo '<span class="pagemenu-selected">';
        }

        echo generate_link(ucwords($type), $link_array, array('group' => $type));
        if ($vars['group'] == $type) {
            echo '</span>';
        }

        $sep = ' | ';
    }
}

unset($sep);

print_optionbar_end();

$graph_enable = $graph_enable[$vars['group']];

foreach ($graph_enable as $graph => $entry) {
    $graph_array = array();
    if ($graph_enable[$graph]) {
        $graph_title         = $config['graph_types']['device'][$graph]['descr'];
        $graph_array['type'] = 'device_'.$graph;

        include 'includes/print-device-graph.php';
    }
}

$pagetitle[] = 'Graphs';
