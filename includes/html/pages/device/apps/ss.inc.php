<?php

require_once 'includes/ss-shared.inc.php';

$allowed_sockets = $app->data['allowed_sockets'] ?? [];
$allowed_afs = $app->data['allowed_afs'] ?? [];

/**
 * Builds the graphs variable
 *
 * @param  string  $gen_type
 * @param  associative-array  $ss_netid_mapper
 * @param  array  $graphs
 * @return $graphs
 */
function ss_graph_builder($gen_type, $ss_netid_mapper, $allowed_sockets, $graphs)
{
    $graph_name = 'ss_' . $gen_type;
    $graphs[$graph_name]['type'] = $gen_type;

    // Build graphs for socket types and the netlink address family.
    if (! array_key_exists($gen_type, $ss_netid_mapper)) {
        $graph_descr = strtoupper($gen_type) . ' Sockets\' Statuses';
        $graphs[$graph_name]['desc'] = $graph_descr;
    } else {
        // Build graphs for address family's netids.
        foreach ($ss_netid_mapper[$gen_type] as $netid) {
            // Don't build graphs for socket types (netids)
            // that have been filtered out.
            if (! in_array($netid, $allowed_sockets)) {
                continue;
            }
            $graph_descr = strtoupper($gen_type) . ' ' . strtoupper($netid) . ' Sockets\' Statuses';
            $graphs[$graph_name]['netid_statuses'][$netid]['desc'] = $graph_descr;
        }
    }

    return $graphs;
}

/**
 * Builds a graph array and outputs the graph.
 *
 * @param  string  $gen_type
 * @param  string  $app_id
 * @param  null|string  $netid
 * @param  string  $graph_desc
 */
function ss_graph_printer($gen_type, $app_id, $netid, $graph_desc)
{
    $graph_type = $gen_type;
    $graph_array['height'] = '100';
    $graph_array['width'] = '215';
    $graph_array['to'] = time();
    $graph_array['id'] = $app_id;
    $graph_array['type'] = 'application_' . $gen_type;
    if (! is_null($netid)) {
        $graph_array['netid'] = $netid;
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
    'app' => 'ss',
];

print_optionbar_start();

echo generate_link('All Socket Types\' Statuses', $link_array) . ' | ';

$first_section_listed = false;
foreach ($ss_section_list as $gen_type) {
    // Don't generate links for socket types or address
    // families that have been filtered out.
    if (! in_array($gen_type, $allowed_sockets) && ! in_array($gen_type, $allowed_afs)) {
        continue;
    }

    if (! $first_section_listed) {
        $first_section_listed = true;
    } else {
        echo ', ';
    }

    echo generate_link(strtoupper($gen_type), $link_array, ['section' => $gen_type]);
    $i++;
}

print_optionbar_end();

$graphs = [];

// Build graphs variable
if (isset($vars['section'])) {
    // Build graphs for the individual socket type sections.
    $graphs = ss_graph_builder($vars['section'], $ss_netid_mapper, $allowed_sockets, $graphs);
} else {
    // Build graphs for the combined socket statuses section.
    foreach ($ss_section_list as $gen_type) {
        // Don't generate links for socket types or address
        // families that have been filtered out.
        if (! in_array($gen_type, $allowed_sockets) && ! in_array($gen_type, $allowed_afs)) {
            continue;
        }
        $graphs = ss_graph_builder($gen_type, $ss_netid_mapper, $allowed_sockets, $graphs);
    }
}

// Display the built graphs
foreach ($graphs as $gen_type => $gen_values) {
    // Print graphs for address families with netids.
    if (array_key_exists($gen_values['type'], $ss_netid_mapper)) {
        foreach ($gen_values['netid_statuses'] as $netid => $text) {
            ss_graph_printer($gen_type, $app['app_id'], $netid, $text['desc']);
        }
    } else {
        // Print graphs for socket types and the netlink address family.
        ss_graph_printer($gen_type, $app['app_id'], null, $gen_values['desc']);
    }
}
