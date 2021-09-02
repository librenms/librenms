<?php

print_optionbar_start();

echo "<span style='font-weight: bold;'>Serverfarms</span> &#187; ";

// $auth = TRUE;
$menu_options = ['basic' => 'Basic'];

if (! $_GET['opta']) {
    $_GET['opta'] = 'basic';
}

$sep = '';
foreach ($menu_options as $option => $text) {
    if ($_GET['type'] == $option) {
        echo "<span class='pagemenu-selected'>";
    }

    echo '<a href="' . \LibreNMS\Util\Url::generate($vars, ['type' => $option]) . '">' . $text . '</a>';
    if ($_GET['type'] == $option) {
        echo '</span>';
    }

    echo ' | ';
}

unset($sep);

echo ' Graphs: ';

$graph_types = [
    'bits'  => 'Bits',
    'pkts'  => 'Packets',
    'conns' => 'Connections',
];

foreach ($graph_types as $type => $descr) {
    echo "$type_sep";
    if ($_GET['opte'] == $type) {
        echo "<span class='pagemenu-selected'>";
    }

    echo '<a href="device/device=' . $device['device_id'] . '/tab=routing/type=loadbalancer_vservers/graphs/' . $type . '/">' . $descr . '</a>';
    echo '<a href="' . \LibreNMS\Util\Url::generate($vars, ['type' => 'loadbalancer_ace_vservers']) . '">' . $text . '</a>';
    if ($_GET['opte'] == $type) {
        echo '</span>';
    }

    $type_sep = ' | ';
}

print_optionbar_end();

echo "<div style='margin: 5px;'><table border=0 cellspacing=0 cellpadding=0 width=100%>";
$i = '0';
foreach (dbFetchRows('SELECT * FROM `loadbalancer_vservers` WHERE `device_id` = ? ORDER BY `classmap`', [$device['device_id']]) as $vserver) {
    if (is_integer($i / 2)) {
        $bg_colour = \LibreNMS\Config::get('list_colour.even');
    } else {
        $bg_colour = \LibreNMS\Config::get('list_colour.odd');
    }

    if ($vserver['serverstate'] == 'inService') {
        $vserver_class = 'green';
    } else {
        $vserver_class = 'red';
    }

    echo "<tr bgcolor='$bg_colour'>";
    // echo("<td width=320 class=list-large>" . $tunnel['local_addr'] . "  &#187;  " . $tunnel['peer_addr'] . "</a></td>");
    echo '<td width=700 class=list-small>' . $vserver['classmap'] . '</a></td>';
    // echo("<td width=150 class=box-desc>" . $rserver['farm_id'] . "</td>");
    echo "<td width=230 class=list-small><span class='" . $vserver_class . "'>" . $vserver['serverstate'] . '</span></td>';
    echo '</tr>';
    if ($_GET['type'] == 'graphs') {
        echo '<tr class="list-bold">';
        echo '<td colspan = 3>';
        $graph_type = 'vserver_' . $_GET['opte'];

        $graph_array['height'] = '100';
        $graph_array['width'] = '215';
        $graph_array['to'] = \LibreNMS\Config::get('time.now');
        $graph_array['id'] = $vserver['classmap_id'];
        $graph_array['type'] = $graph_type;

        include 'includes/html/print-graphrow.inc.php';

        echo '
            </td>
            </tr>';
    }

    echo '</td>';
    echo '</tr>';

    $i++;
}//end foreach

echo '</table></div>';
