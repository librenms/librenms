<?php

if (is_numeric($vars['vsvr'])) {
    // print_optionbar_start();
    // echo("<span style='font-weight: bold;'>VServer</span> &#187; ");
    // echo('<a href="'.generate_url($vars, array('vsvr' => NULL)).'">All</a>');
    // print_optionbar_end();
    $graph_types = [
        'bits'    => 'Bits',
        'pkts'    => 'Packets',
        'conns'   => 'Connections',
        'reqs'    => 'Requests',
        'hitmiss' => 'Hit/Miss',
    ];

    $i = 0;

    echo "<div style='margin: 0px;'><table class='table'>";
    // Table header
    echo '<tr><th width=320>VServer</th><th width=320>VIP and port</th><th width=100>State</th>';
    echo '<th width=320>Type</th><th width=320>Inbound traffic</th><th width=320>Outbound traffic</th></tr>';
    // Vserver graphs
    // Can this really return more than one row?
    $vservers = dbFetchRows('SELECT * FROM `netscaler_vservers` WHERE `device_id` = ? AND `vsvr_id` = ? ORDER BY `vsvr_name`', [$device['device_id'], $vars['vsvr']]);
    foreach ($vservers as $vsvr) {
        if (is_integer($i / 2)) {
            $bg_colour = \LibreNMS\Config::get('list_colour.even');
        } else {
            $bg_colour = \LibreNMS\Config::get('list_colour.odd');
        }

        if ($vsvr['vsvr_state'] == 'up') {
            $vsvr_label = 'success';
        } elseif ($vsvr['vsvr_state'] == 'down') {
            $vsvr_label = 'danger';
        } else {
            $vsvr_label = 'default';
        }

        echo "<tr bgcolor='$bg_colour'>";
        echo '<td><a href="' . \LibreNMS\Util\Url::generate($vars, ['vsvr' => $vsvr['vsvr_id'], 'view' => null, 'graph' => null]) . '">' . $vsvr['vsvr_name'] . '</a></td>';
        echo '<td>' . $vsvr['vsvr_ip'] . ':' . $vsvr['vsvr_port'] . '</td>';
        echo "<td><span class='label label-" . $vsvr_label . "'>" . $vsvr['vsvr_state'] . '</span></td>';
        echo '<td><span class="label label-default">' . $vsvr['vsvr_type'] . '</span></td>';
        echo '<td>' . \LibreNMS\Util\Number::formatSi(($vsvr['vsvr_bps_in'] * 8), 2, 3, '') . 'bps</a></td>';
        echo '<td>' . \LibreNMS\Util\Number::formatSi(($vsvr['vsvr_bps_out'] * 8), 2, 3, '') . 'bps</a></td>';
        echo '</tr>';

        foreach ($graph_types as $graph_type => $graph_text) {
            $i++;
            echo '<tr class="list-bold" bgcolor="' . $bg_colour . '">';
            echo '<td colspan="6">';
            $graph_type = 'netscalervsvr_' . $graph_type;
            $graph_array['height'] = '100';
            $graph_array['width'] = '213';
            $graph_array['to'] = \LibreNMS\Config::get('time.now');
            $graph_array['id'] = $vsvr['vsvr_id'];
            $graph_array['type'] = $graph_type;

            echo '<h3>' . $graph_text . '</h3>';

            include 'includes/html/print-graphrow.inc.php';

            echo '
    </td>
    </tr>';
        }
    }//end foreach

    echo '</table></div>';
} else {
    print_optionbar_start();

    echo "<span style='font-weight: bold;'>VServers</span> &#187; ";

    $menu_options = ['basic' => 'Basic'];

    if (! $vars['view']) {
        $vars['view'] = 'basic';
    }

    $sep = '';
    foreach ($menu_options as $option => $text) {
        if ($vars['view'] == $option) {
            echo "<span class='pagemenu-selected'>";
        }

        echo '<a href="' . \LibreNMS\Util\Url::generate($vars, ['view' => 'basic', 'graph' => null]) . '">' . $text . '</a>';
        if ($vars['view'] == $option) {
            echo '</span>';
        }

        echo ' | ';
    }

    unset($sep);
    echo ' Graphs: ';
    $graph_types = [
        'bits'    => 'Bits',
        'pkts'    => 'Packets',
        'conns'   => 'Connections',
        'reqs'    => 'Requests',
        'hitmiss' => 'Hit/Miss',
    ];

    foreach ($graph_types as $type => $descr) {
        echo "$type_sep";
        if ($vars['graph'] == $type) {
            echo "<span class='pagemenu-selected'>";
        }

        echo '<a href="' . \LibreNMS\Util\Url::generate($vars, ['view' => 'graphs', 'graph' => $type]) . '">' . $descr . '</a>';
        if ($vars['graph'] == $type) {
            echo '</span>';
        }

        $type_sep = ' | ';
    }

    print_optionbar_end();

    echo "<div style='margin: 0px;'><table class='table'>";
    // Table header
    echo '<tr><th width=320><a href=' . \LibreNMS\Util\Url::generate($vars, ['sort' => 'vsvr_name']) . '>VServer</a></th>';
    echo '<th width=320>VIP and port</th><th width=100>State</th><th width=320>Type</th>';
    echo '<th width=320><a href=' . \LibreNMS\Util\Url::generate($vars, ['sort' => 'vsvr_bps_in']) . '>Inbound traffic</a></th>';
    echo '<th width=320><a href=' . \LibreNMS\Util\Url::generate($vars, ['sort' => 'vsvr_bps_out']) . '>Outbound traffic</a></th></tr>';
    // Vserver list
    $vservers = dbFetchRows('SELECT * FROM `netscaler_vservers` WHERE `device_id` = ? ORDER BY `vsvr_name`', [$device['device_id']]);

    // Vserver sorting
    $valid_sort_keys = ['vsvr_bps_in', 'vsvr_bps_out', 'vsrv_name'];
    if (isset($vars['sort']) && in_array($vars['sort'], $valid_sort_keys)) {
        $sort_key = $vars['sort'];
    } else {
        $sort_key = 'vsvr_name';
    }
    switch ($sort_key) {
        case 'vsvr_bps_in':
        case 'vsvr_bps_out':
            $sort_direction = SORT_DESC;
            break;
        default:
            $sort_direction = SORT_ASC;
    }
    $vservers = array_sort_by_column($vservers, $sort_key, $sort_direction);

    $i = '0';
    foreach ($vservers as $vsvr) {
        if (is_integer($i / 2)) {
            $bg_colour = \LibreNMS\Config::get('list_colour.even');
        } else {
            $bg_colour = \LibreNMS\Config::get('list_colour.odd');
        }

        if ($vsvr['vsvr_state'] == 'up') {
            $vsvr_label = 'success';
        } elseif ($vsvr['vsvr_state'] == 'down') {
            $vsvr_label = 'danger';
        } else {
            $vsvr_label = 'default';
        }

        echo "<tr bgcolor='$bg_colour'>";
        echo '<td><a href="' . \LibreNMS\Util\Url::generate($vars, ['vsvr' => $vsvr['vsvr_id'], 'view' => null, 'graph' => null]) . '">' . $vsvr['vsvr_name'] . '</a></td>';
        echo '<td>' . $vsvr['vsvr_ip'] . ':' . $vsvr['vsvr_port'] . '</td>';
        echo "<td><span class='label label-" . $vsvr_label . "'>" . $vsvr['vsvr_state'] . '</span></td>';
        echo '<td><span class="label label-default">' . $vsvr['vsvr_type'] . '</span></td>';
        echo '<td>' . \LibreNMS\Util\Number::formatSi(($vsvr['vsvr_bps_in'] * 8), 2, 3, '') . 'bps</a></td>';
        echo '<td>' . \LibreNMS\Util\Number::formatSi(($vsvr['vsvr_bps_out'] * 8), 2, 3, '') . 'bps</a></td>';
        echo '</tr>';
        if ($vars['view'] == 'graphs') {
            echo '<tr class="list-bold" bgcolor="' . $bg_colour . '">';
            echo '<td colspan="6">';
            $graph_type = 'netscalervsvr_' . $vars['graph'];
            $graph_array['height'] = '100';
            $graph_array['width'] = '213';
            $graph_array['to'] = \LibreNMS\Config::get('time.now');
            $graph_array['id'] = $vsvr['vsvr_id'];
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
}//end if
