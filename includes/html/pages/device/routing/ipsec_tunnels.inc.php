<?php

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'routing',
    'proto' => 'ipsec_tunnels',
];

print_optionbar_start();

echo "<span style='font-weight: bold;'>IPSEC Tunnels</span> &#187; ";

$menu_options = [
    'basic' => 'Basic',
];

if (! isset($vars['view'])) {
    $vars['view'] = 'basic';
}

echo "<span style='font-weight: bold;'>VRFs</span> &#187; ";

$menu_options = [
    'basic' => 'Basic',
];

if (! $_GET['opta']) {
    $_GET['opta'] = 'basic';
}

$sep = '';
foreach ($menu_options as $option => $text) {
    if ($vars['view'] == $option) {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link($text, $link_array, [
        'view' => $option,
    ]);
    if ($vars['view'] == $option) {
        echo '</span>';
    }

    echo ' | ';
}

echo ' Graphs: ';

$graph_types = [
    'bits' => 'Bits',
    'pkts' => 'Packets',
];

foreach ($graph_types as $type => $descr) {
    echo "$type_sep";
    if ($vars['graph'] == $type) {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link($descr, $link_array, [
        'view' => 'graphs',
        'graph' => $type,
    ]);
    if ($vars['graph'] == $type) {
        echo '</span>';
    }

    $type_sep = ' | ';
}

print_optionbar_end();

$tunnel = dbFetchRows('SELECT * FROM `ipsec_tunnels` WHERE `device_id` = ? ORDER BY `peer_addr`', [
    $device['device_id'],
]);

if (is_null($vars['graph'])) {
    $tunnel_label = 'warning';
    echo '<table class="table table-condensed table-hover">
    <thead>
      <tr>
        <th>Local Identity</th>
        <th>Remote Identity</th>
        <th>Name</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>';

    foreach ($tunnel as $entry) {
        $local_addr = preg_replace('/\b0+(?=\d)/', '', htmlentities($entry['local_addr']));
        $remote_addr = preg_replace('/\b0+(?=\d)/', '', htmlentities($entry['peer_addr']));

        if ($tunnel['tunnel_status'] = 'active') {
            $tunnel_label = 'success';
        }
        echo '<tr>
            <td>' . $local_addr . '</td>
            <td>' . $remote_addr . '</td>
            <td>' . htmlentities($entry['tunnel_name']) . '</td>
            <td><span class="label label-' . $tunnel_label . '">' . htmlentities($entry['tunnel_status']) . '</span></td>
        </tr>';
    }

    echo '</tbody>
    </table>';
} else {
    foreach ($tunnel as $entry) {
        $local_addr = preg_replace('/\b0+(?=\d)/', '', htmlentities($entry['local_addr']));
        $remote_addr = preg_replace('/\b0+(?=\d)/', '', htmlentities($entry['peer_addr']));

        $graph_type = 'ipsectunnel_' . $vars['graph'];
        $graph_array['height'] = '100';
        $graph_array['width'] = '215';
        $graph_array['to'] = \LibreNMS\Config::get('time.now');
        $graph_array['id'] = $entry['tunnel_id'];
        $graph_array['type'] = $graph_type;
        echo '<div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">' . $local_addr . '  &#187;  ' . $remote_addr . '</h3>
            </div>
            <div class="panel-body">';
        echo "<div class='row'>";
        include 'includes/html/print-graphrow.inc.php';

        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
}
