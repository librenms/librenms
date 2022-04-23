<?php

print_optionbar_start();

$link_array = [
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'routing',
    'proto'  => 'cef',
];

if (! isset($vars['view'])) {
    $vars['view'] = 'basic';
}

echo '<span style="font-weight: bold;">CEF</span> &#187; ';

if ($vars['view'] == 'basic') {
    echo "<span class='pagemenu-selected'>";
}

echo generate_link('Basic', $link_array, ['view' => 'basic']);
if ($vars['view'] == 'basic') {
    echo '</span>';
}

echo ' | ';

if ($vars['view'] == 'graphs') {
    echo "<span class='pagemenu-selected'>";
}

echo generate_link('Graphs', $link_array, ['view' => 'graphs']);
if ($vars['view'] == 'graphs') {
    echo '</span>';
}

print_optionbar_end();

echo '<div id="content">
    <table  border="0" cellspacing="0" cellpadding="5" width="100%">';

echo '<tr><th><a title="Physical hardware entity">Entity</a></th>
    <th><a title="Address Family">AFI</a></th>
    <th><a title="CEF Switching Path">Path</a></th>
    <th><a title="Number of packets dropped.">Drop</a></th>
    <th><a title="Number of packets that could not be switched in the normal path and were punted to the next-fastest switching vector.">Punt</a></th>
    <th><a title="Number of packets that could not be switched in the normal path and were punted to the host.<br />For switch paths other than a centralized turbo switch path, punt and punt2host function the same way. With punt2host from a centralized turbo switch path (PAS and RSP), punt will punt the packet to LES, but punt2host will bypass LES and punt directly to process switching.">Punt2Host</a></th>
    </tr>';

$i = 0;

foreach (dbFetchRows('SELECT * FROM `cef_switching` WHERE `device_id` = ?  ORDER BY `entPhysicalIndex`, `afi`, `cef_index`', [$device['device_id']]) as $cef) {
    $entity = dbFetchRow('SELECT * FROM `entPhysical` WHERE device_id = ? AND `entPhysicalIndex` = ?', [$device['device_id'], $cef['entPhysicalIndex']]);

    if (! is_integer($i / 2)) {
        $bg_colour = \LibreNMS\Config::get('list_colour.even');
    } else {
        $bg_colour = \LibreNMS\Config::get('list_colour.odd');
    }

    $interval = ($cef['updated'] - $cef['updated_prev']);

    if (! $entity['entPhysicalModelName'] && $entity['entPhysicalContainedIn']) {
        $parent_entity = dbFetchRow('SELECT * FROM `entPhysical` WHERE device_id = ? AND `entPhysicalIndex` = ?', [$device['device_id'], $entity['entPhysicalContainedIn']]);
        $entity_descr = $entity['entPhysicalName'] . ' (' . $parent_entity['entPhysicalModelName'] . ')';
    } else {
        $entity_descr = $entity['entPhysicalName'] . ' (' . $entity['entPhysicalModelName'] . ')';
    }

    echo "<tr bgcolor=$bg_colour><td>" . $entity_descr . '</td>
        <td>' . $cef['afi'] . '</td>
        <td>';

    switch ($cef['cef_path']) {
        case 'RP RIB':
            echo '<a title="Process switching with CEF assistance.">RP RIB</a>';
            break;

        case 'RP LES':
            echo '<a title="Low-end switching. Centralized CEF switch path.">RP LES</a>';
            break;

        case 'RP PAS':
            echo '<a title="CEF turbo switch path.">RP PAS</a>';
            break;

        default:
            echo $cef['cef_path'];
    }

    echo '</td>';
    echo '<td>' . \LibreNMS\Util\Number::formatSi($cef['drop'], 2, 3, '');
    if ($cef['drop'] > $cef['drop_prev']) {
        echo " <span style='color:red;'>(" . round((($cef['drop'] - $cef['drop_prev']) / $interval), 2) . '/sec)</span>';
    }

    echo '</td>';
    echo '<td>' . \LibreNMS\Util\Number::formatSi($cef['punt'], 2, 3, '');
    if ($cef['punt'] > $cef['punt_prev']) {
        echo " <span style='color:red;'>(" . round((($cef['punt'] - $cef['punt_prev']) / $interval), 2) . '/sec)</span>';
    }

    echo '</td>';
    echo '<td>' . \LibreNMS\Util\Number::formatSi($cef['punt2host'], 2, 3, '');
    if ($cef['punt2host'] > $cef['punt2host_prev']) {
        echo " <span style='color:red;'>(" . round((($cef['punt2host'] - $cef['punt2host_prev']) / $interval), 2) . '/sec)</span>';
    }

    echo '</td>';

    echo '</tr>
    ';

    if ($vars['view'] == 'graphs') {
        $graph_array['height'] = '100';
        $graph_array['width'] = '215';
        $graph_array['to'] = \LibreNMS\Config::get('time.now');
        $graph_array['id'] = $cef['cef_switching_id'];
        $graph_array['type'] = 'cefswitching_graph';

        echo "<tr bgcolor='$bg_colour'><td colspan=6>";

        include 'includes/html/print-graphrow.inc.php';

        echo '</td></tr>';
    }

    $i++;
}

echo '</table></div>';
