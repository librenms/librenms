<?php

// Graphs are printed in the order they exist in \LibreNMS\Config::get('graph_types')
$link_array = [
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'munin',
];

$bg = '#ffffff';

echo '<div style="clear: both;">';

print_optionbar_start();

echo "<span style='font-weight: bold;'>Munin</span> &#187; ";

$sep = '';

foreach (dbFetchRows('SELECT * FROM munin_plugins WHERE device_id = ? ORDER BY mplug_category, mplug_type', [$device['device_id']]) as $mplug) {
    // if (strlen($mplug['mplug_category']) == 0) { $mplug['mplug_category'] = "general"; } else {  }
    $graph_enable[$mplug['mplug_category']][$mplug['mplug_type']]['id'] = $mplug['mplug_id'];
    $graph_enable[$mplug['mplug_category']][$mplug['mplug_type']]['title'] = $mplug['mplug_title'];
    $graph_enable[$mplug['mplug_category']][$mplug['mplug_type']]['plugin'] = $mplug['mplug_type'];
}

foreach ($graph_enable as $section => $nothing) {
    if (isset($graph_enable) && is_array($graph_enable[$section])) {
        $type = strtolower($section);
        if (! $vars['group']) {
            $vars['group'] = $type;
        }

        echo $sep;
        if ($vars['group'] == $type) {
            echo '<span class="pagemenu-selected">';
        }

        echo generate_link(ucwords($type), $link_array, ['group' => $type]);
        if ($vars['group'] == $type) {
            echo '</span>';
        }

        $sep = ' | ';
    }
}

unset($sep);
print_optionbar_end();

$graph_enable = $graph_enable[$vars['group']];

// foreach (\LibreNMS\Config::get('graph_types.device') as $graph => $entry)
foreach ($graph_enable as $graph => $entry) {
    $graph_array = [];
    if ($graph_enable[$graph]) {
        if (! empty($entry['plugin'])) {
            $graph_title = $entry['title'];
            $graph_array['type'] = 'munin_graph';
            $graph_array['device'] = $device['device_id'];
            $graph_array['plugin'] = $entry['plugin'];
        } else {
            $graph_title = \LibreNMS\Config::get("graph_types.device.$graph.descr");
            $graph_array['type'] = 'device_' . $graph;
        }

        include 'includes/html/print-device-graph.php';
    }
}

$pagetitle[] = 'Graphs';
