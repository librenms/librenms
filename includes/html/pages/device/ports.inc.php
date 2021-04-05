<?php

if ($vars['view'] == 'graphs' || $vars['view'] == 'minigraphs') {
    if (isset($vars['graph'])) {
        $graph_type = 'port_' . $vars['graph'];
    } else {
        $graph_type = 'port_bits';
    }
}

if (! $vars['view']) {
    $vars['view'] = trim(\LibreNMS\Config::get('ports_page_default'), '/');
}

$link_array = [
    'page'   => 'device',
    'device' => $device['device_id'],
    'tab'    => 'ports',
];

print_optionbar_start();

$menu_options['basic'] = 'Basic';
$menu_options['details'] = 'Details';
$menu_options['arp'] = 'ARP Table';
$menu_options['fdb'] = 'FDB Table';

if (dbFetchCell("SELECT * FROM links AS L, ports AS I WHERE I.device_id = '" . $device['device_id'] . "' AND I.port_id = L.local_port_id")) {
    $menu_options['neighbours'] = 'Neighbours';
}

if (dbFetchCell("SELECT COUNT(*) FROM `ports` WHERE `ifType` = 'adsl'")) {
    $menu_options['adsl'] = 'ADSL';
}

$sep = '';
foreach ($menu_options as $option => $text) {
    echo $sep;
    if ($vars['view'] == $option) {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link($text, $link_array, ['view' => $option]);
    if ($vars['view'] == $option) {
        echo '</span>';
    }

    $sep = ' | ';
}

unset($sep);

echo ' | Graphs: ';

$graph_types = [
    'bits'      => 'Bits',
    'upkts'     => 'Unicast Packets',
    'nupkts'    => 'Non-Unicast Packets',
    'errors'    => 'Errors',
];

if (\LibreNMS\Config::get('enable_ports_etherlike')) {
    $graph_types['etherlike'] = 'Etherlike';
}

foreach ($graph_types as $type => $descr) {
    echo "$type_sep";
    if ($vars['graph'] == $type && $vars['view'] == 'graphs') {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link($descr, $link_array, ['view' => 'graphs', 'graph' => $type]);
    if ($vars['graph'] == $type && $vars['view'] == 'graphs') {
        echo '</span>';
    }

    echo ' (';
    if ($vars['graph'] == $type && $vars['view'] == 'minigraphs') {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link('Mini', $link_array, ['view' => 'minigraphs', 'graph' => $type]);
    if ($vars['graph'] == $type && $vars['view'] == 'minigraphs') {
        echo '</span>';
    }

    echo ')';
    $type_sep = ' | ';
}//end foreach

print_optionbar_end();

if ($vars['view'] == 'minigraphs') {
    $timeperiods = [
        '-1day',
        '-1week',
        '-1month',
        '-1year',
    ];
    $from = '-1day';
    echo "<div style='display: block; clear: both; margin: auto; min-height: 500px;'>";
    unset($seperator);

    // FIXME - FIX THIS. UGLY.
    foreach (dbFetchRows('select * from ports WHERE device_id = ? AND `disabled` = 0 ORDER BY ifIndex', [$device['device_id']]) as $port) {
        $port = cleanPort($port, $device);
        echo "<div class='minigraph-div'><div style='font-weight: bold;'>" . makeshortif($port['ifDescr']) . '</div>
            <a href="' . generate_port_url($port) . "\" onmouseover=\"return overlib('<div class=\'overlib-content\'>\
      	    <div class=\'overlib-text\'>" . $device['hostname'] . ' - ' . $port['ifDescr'] . "</div>\
            <span class=\'overlib-title\'>" . $port['ifAlias'] . "</span>\
            <img src=\'graph.php?type=" . $graph_type . '&amp;id=' . $port['port_id'] . '&amp;from=' . $from . '&amp;to=' . \LibreNMS\Config::get('time.now') . "&amp;width=450&amp;height=150\'>\
            </div>\
            ', CENTER, LEFT, FGCOLOR, '#e5e5e5', BGCOLOR, '#e5e5e5', WIDTH, 400, HEIGHT, 150);\" onmouseout=\"return nd();\"  >" . "<img src='graph.php?type=" . $graph_type . '&amp;id=' . $port['port_id'] . '&amp;from=' . $from . '&amp;to=' . \LibreNMS\Config::get('time.now') . "&amp;width=180&amp;height=45&amp;legend=no'>
            </a>
            <div style='font-size: 9px;'>" . substr(short_port_descr($port['ifAlias']), 0, 32) . '</div>
            </div>';
    }

    echo '</div>';
} elseif ($vars['view'] == 'arp' || $vars['view'] == 'adsl' || $vars['view'] == 'neighbours' || $vars['view'] == 'fdb') {
    include 'ports/' . $vars['view'] . '.inc.php';
} else {
    if ($vars['view'] == 'details') {
        $port_details = 1;
    } ?>
<div style='margin: 0px;'><table class='table'>
  <tr>
    <th width="350"><A href="<?php echo \LibreNMS\Util\Url::generate($vars, ['sort' => 'port']); ?>">Port</a></th>
    <th width="100">Port Group</a></th>
    <th width="100"></th>
    <th width="120"><a href="<?php echo \LibreNMS\Util\Url::generate($vars, ['sort' => 'traffic']); ?>">Traffic</a></th>
    <th width="75">Speed</th>
    <th width="100">Media</th>
    <th width="100">Mac Address</th>
    <th width="375"></th>
  </tr>
    <?php

    $i = '1';

    global $port_cache, $port_index_cache;

    $ports = dbFetchRows("SELECT * FROM `ports` WHERE `device_id` = ? AND `deleted` = '0' AND `disabled` = 0 ORDER BY `ifIndex` ASC", [$device['device_id']]);
    // As we've dragged the whole database, lets pre-populate our caches :)
    // FIXME - we should probably split the fetching of link/stack/etc into functions and cache them here too to cut down on single row queries.

    foreach ($ports as $key => $port) {
        $port_cache[$port['port_id']] = $port;
        $port_index_cache[$port['device_id']][$port['ifIndex']] = $port;
        $ports[$key]['ifOctets_rate'] = $port['ifInOctets_rate'] + $port['ifOutOctets_rate'];
    }

    switch ($vars['sort']) {
        case 'traffic':
            $ports = array_sort_by_column($ports, 'ifOctets_rate', SORT_DESC);
            break;
        default:
            $ports = array_sort_by_column($ports, 'ifIndex', SORT_ASC);
            break;
    }

    foreach ($ports as $port) {
        include 'includes/html/print-interface.inc.php';
        $i++;
    }

    echo '</table></div>';
}//end if

$pagetitle[] = 'Ports';
