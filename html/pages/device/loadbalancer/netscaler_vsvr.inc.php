<?php

if (is_numeric($vars['vsvr'])) {
    // print_optionbar_start();
    // echo("<span style='font-weight: bold;'>VServer</span> &#187; ");
    // echo('<a href="'.generate_url($vars, array('vsvr' => NULL)).'">All</a>');
    // print_optionbar_end();
    $graph_types = array(
                    'bits'    => 'Bits',
                    'pkts'    => 'Packets',
                    'conns'   => 'Connections',
                    'reqs'    => 'Requests',
                    'hitmiss' => 'Hit/Miss',
                   );

    $i = 0;

    echo "<div style='margin: 5px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>";
    foreach (dbFetchRows('SELECT * FROM `netscaler_vservers` WHERE `device_id` = ? AND `vsvr_id` = ? ORDER BY `vsvr_name`', array($device['device_id'], $vars['vsvr'])) as $vsvr) {
        if (is_integer($i / 2)) {
            $bg_colour = $list_colour_a;
        } else {
            $bg_colour = $list_colour_b;
        }

        if ($vsvr['vsvr_state'] == 'up') {
            $vsvr_class = 'green';
        } else {
            $vsvr_class = 'red';
        }

        echo "<tr bgcolor='$bg_colour'>";
        echo '<td width=320 class=list-large><a href="'.generate_url($vars, array('vsvr' => $vsvr['vsvr_id'], 'view' => null, 'graph' => null)).'">'.$vsvr['vsvr_name'].'</a></td>';
        echo '<td width=320 class=list-small>'.$vsvr['vsvr_ip'].':'.$vsvr['vsvr_port'].'</a></td>';
        echo "<td width=100 class=list-small><span class='".$vsvr_class."'>".$vsvr['vsvr_state'].'</span></td>';
        echo ('<td width=320 class=list-small>'.format_si(($vsvr['vsvr_bps_in'] * 8)).'bps</a></td>');
        echo ('<td width=320 class=list-small>'.format_si(($vsvr['vsvr_bps_out'] * 8)).'bps</a></td>');
        echo '</tr>';

        foreach ($graph_types as $graph_type => $graph_text) {
            $i++;
            echo '<tr class="list-bold" bgcolor="'.$bg_colour.'">';
            echo '<td colspan="5">';
            $graph_type            = 'netscalervsvr_'.$graph_type;
            $graph_array['height'] = '100';
            $graph_array['width']  = '213';
            $graph_array['to']     = $config['time']['now'];
            $graph_array['id']     = $vsvr['vsvr_id'];
            $graph_array['type']   = $graph_type;

            echo '<h3>'.$graph_text.'</h3>';

            include 'includes/print-graphrow.inc.php';

            echo '
    </td>
    </tr>';
        }
    }//end foreach

    echo '</table></div>';
} else {
    print_optionbar_start();

    echo "<span style='font-weight: bold;'>VServers</span> &#187; ";

    $menu_options = array('basic' => 'Basic');

    if (!$vars['view']) {
        $vars['view'] = 'basic';
    }

    $sep = '';
    foreach ($menu_options as $option => $text) {
        if ($vars['view'] == $option) {
            echo "<span class='pagemenu-selected'>";
        }

        echo '<a href="'.generate_url($vars, array('view' => 'basic', 'graph' => null)).'">'.$text.'</a>';
        if ($vars['view'] == $option) {
            echo '</span>';
        }

        echo ' | ';
    }

    unset($sep);
    echo ' Graphs: ';
    $graph_types = array(
                    'bits'    => 'Bits',
                    'pkts'    => 'Packets',
                    'conns'   => 'Connections',
                    'reqs'    => 'Requests',
                    'hitmiss' => 'Hit/Miss',
                   );

    foreach ($graph_types as $type => $descr) {
        echo "$type_sep";
        if ($vars['graph'] == $type) {
            echo "<span class='pagemenu-selected'>";
        }

        echo '<a href="'.generate_url($vars, array('view' => 'graphs', 'graph' => $type)).'">'.$descr.'</a>';
        if ($vars['graph'] == $type) {
            echo '</span>';
        }

        $type_sep = ' | ';
    }

    print_optionbar_end();

    echo "<div style='margin: 5px;'><table border=0 cellspacing=0 cellpadding=5 width=100%>";
    $i = '0';
    foreach (dbFetchRows('SELECT * FROM `netscaler_vservers` WHERE `device_id` = ? ORDER BY `vsvr_name`', array($device['device_id'])) as $vsvr) {
        if (is_integer($i / 2)) {
            $bg_colour = $list_colour_a;
        } else {
            $bg_colour = $list_colour_b;
        }

        if ($vsvr['vsvr_state'] == 'up') {
            $vsvr_class = 'green';
        } else {
            $vsvr_class = 'red';
        }

        echo "<tr bgcolor='$bg_colour'>";
        echo '<td width=320 class=list-large><a href="'.generate_url($vars, array('vsvr' => $vsvr['vsvr_id'], 'view' => null, 'graph' => null)).'">'.$vsvr['vsvr_name'].'</a></td>';
        echo '<td width=320 class=list-small>'.$vsvr['vsvr_ip'].':'.$vsvr['vsvr_port'].'</a></td>';
        echo "<td width=100 class=list-small><span class='".$vsvr_class."'>".$vsvr['vsvr_state'].'</span></td>';
        echo ('<td width=320 class=list-small>'.format_si(($vsvr['vsvr_bps_in'] * 8)).'bps</a></td>');
        echo ('<td width=320 class=list-small>'.format_si(($vsvr['vsvr_bps_out'] * 8)).'bps</a></td>');
        echo '</tr>';
        if ($vars['view'] == 'graphs') {
            echo '<tr class="list-bold" bgcolor="'.$bg_colour.'">';
            echo '<td colspan="5">';
            $graph_type            = 'netscalervsvr_'.$vars['graph'];
            $graph_array['height'] = '100';
            $graph_array['width']  = '213';
            $graph_array['to']     = $config['time']['now'];
            $graph_array['id']     = $vsvr['vsvr_id'];
            $graph_array['type']   = $graph_type;

            include 'includes/print-graphrow.inc.php';

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
