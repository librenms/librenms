<?php

use App\Models\Port;
use LibreNMS\Config;
use LibreNMS\Util\Url;

if (empty($vars['view'])) {
    $vars['view'] = trim(Config::get('ports_page_default'), '/');
}

if ($vars['view'] == 'graphs' || $vars['view'] == 'minigraphs') {
    if (isset($vars['graph'])) {
        $graph_type = 'port_' . $vars['graph'];
    } else {
        $graph_type = 'port_bits';
    }
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

if (DeviceCache::getPrimary()->portsAdsl()->exists() || DeviceCache::getPrimary()->portsVdsl()->exists()) {
    $menu_options['xdsl'] = 'xDSL';
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

if (Config::get('enable_ports_etherlike')) {
    $graph_types['etherlike'] = 'Etherlike';
}

$type_sep = '';
$vars['graph'] = $vars['graph'] ?? '';
foreach ($graph_types as $type => $descr) {
    echo $type_sep;
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

    foreach (Port::where('device_id', $device['device_id'])->where('disabled', 0)->orderBy('ifIndex')->get() as $port) {
        echo '<div class="minigraph-div">'
            . Url::portLink($port,
                '<div style="font-weight: bold;">' . $port->getShortLabel() . '</div>' .
                Url::graphTag([
                    'type' => $graph_type,
                    'id' => $port['port_id'],
                    'from' => $from,
                    'width' => 180,
                    'height' => 55,
                    'legend' => 'no',
                ]))
        . '</div>';
    }

    echo '</div>';
} elseif ($vars['view'] == 'arp' || $vars['view'] == 'xdsl' || $vars['view'] == 'neighbours' || $vars['view'] == 'fdb') {
    include 'ports/' . $vars['view'] . '.inc.php';
} else {
    if ($vars['view'] == 'details') {
        $port_details = 1;
    } ?>
<div style='margin: 0px;'><table class='table'>
  <tr>
    <th width="350"><A href="<?php echo Url::generate($vars, ['sort' => 'port']); ?>">Port</a></th>
    <th width="100">Port Group</a></th>
    <th width="100"></th>
    <th width="120"><a href="<?php echo Url::generate($vars, ['sort' => 'traffic']); ?>">Traffic</a></th>
    <th width="75">Speed</th>
    <th width="100">Media</th>
    <th width="100">Mac Address</th>
    <th width="375"></th>
  </tr>
    <?php

    $i = '1';

    global $port_index_cache;

    /** @var \Illuminate\Support\Collection<\App\Models\Port> $ports */
    $ports = DeviceCache::getPrimary()->ports()->orderBy('ifIndex')->isValid()->get();

    // As we've dragged the whole database, lets pre-populate our caches :)
    foreach ($ports as $key => $port) {
        $port_index_cache[$port['device_id']][$port['ifIndex']] = $port;
    }

    if (isset($vars['sort']) && $vars['sort'] == 'traffic') {
        $ports = $ports->sortByDesc(function (Port $port) {
            return $port->ifInOctets_rate + $port->ifOutOctets_rate;
        });
    }

    foreach ($ports as $port) {
        include 'includes/html/print-interface.inc.php';
    }

    echo '</table></div>';
}//end if

$pagetitle[] = 'Ports';
