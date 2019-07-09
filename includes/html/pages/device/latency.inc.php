<?php

print_optionbar_start();

echo "<span style='font-weight: bold;'>Latency</span> &#187; ";

if (count($smokeping_files['in'][$device['hostname']])) {
    $menu_options['incoming'] = 'Incoming';
}

if (count($smokeping_files['out'][$device['hostname']])) {
    $menu_options['outgoing'] = 'Outgoing';
}

$sep = '';
foreach ($menu_options as $option => $text) {
    if (!$vars['view']) {
        $vars['view'] = $option;
    }

    echo $sep;
    if ($vars['view'] == $option) {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link($text, $vars, array('view' => $option));
    if ($vars['view'] == $option) {
        echo '</span>';
    }

    $sep = ' | ';
}

unset($sep);

print_optionbar_end();

echo '<table>';

if ($vars['view'] == 'incoming') {
    if (count($smokeping_files['in'][$device['hostname']])) {
        $graph_array['type']   = 'device_smokeping_in_all_avg';
        $graph_array['device'] = $device['device_id'];
        echo '<tr><td>';
        echo '<h3>Average</h3>';

        include 'includes/html/print-graphrow.inc.php';

        echo '</td></tr>';

        $graph_array['type']   = 'device_smokeping_in_all';
        $graph_array['device'] = $device['device_id'];
        $graph_array['legend'] = 'no';
        echo '<tr><td>';
        echo '<h3>Aggregate</h3>';

        include 'includes/html/print-graphrow.inc.php';

        echo '</td></tr>';

        unset($graph_array['legend']);

        ksort($smokeping_files['in'][$device['hostname']]);
        foreach ($smokeping_files['in'][$device['hostname']] as $src => $host) {
            $hostname = str_replace('.rrd', '', $host);
            $host     = device_by_name($src);
            if (\LibreNMS\Config::get('smokeping.integration') === true) {
                $dest = device_by_name(str_replace("_", ".", $hostname));
            } else {
                $dest = $host;
            }
            if (is_numeric($host['device_id'])) {
                echo '<tr><td>';
                echo '<h3>'.generate_device_link($dest).'</h3>';
                $graph_array['type']   = 'smokeping_in';
                $graph_array['device'] = $device['device_id'];
                $graph_array['src']    = $host['device_id'];

                include 'includes/html/print-graphrow.inc.php';

                echo '</td></tr>';
            }
        }
    }//end if
} elseif ($vars['view'] == 'outgoing') {
    if (count($smokeping_files['out'][$device['hostname']])) {
        $graph_array['type']   = 'device_smokeping_out_all_avg';
        $graph_array['device'] = $device['device_id'];
        echo '<tr><td>';
        echo '<h3>Average</h3>';

        include 'includes/html/print-graphrow.inc.php';

        echo '</td></tr>';

        $graph_array['type']   = 'device_smokeping_out_all';
        $graph_array['device'] = $device['device_id'];
        $graph_array['legend'] = 'no';
        echo '<tr><td>';
        echo '<h3>Aggregate</h3>';

        include 'includes/html/print-graphrow.inc.php';

        echo '</td></tr>';

        unset($graph_array['legend']);

        asort($smokeping_files['out'][$device['hostname']]);
        foreach ($smokeping_files['out'][$device['hostname']] as $host) {
            $hostname       = str_replace('_', '.', str_replace('.rrd', '', $host));
            list($hostname) = explode('~', $hostname);
            $host           = device_by_name($hostname);
            if (is_numeric($host['device_id'])) {
                echo '<tr><td>';
                echo '<h3>'.generate_device_link($host).'</h3>';
                $graph_array['type']   = 'smokeping_out';
                $graph_array['device'] = $device['device_id'];
                $graph_array['dest']   = $host['device_id'];

                include 'includes/html/print-graphrow.inc.php';

                echo '</td></tr>';
            }
        }
    }//end if
}//end if

echo '</table>';

$pagetitle[] = 'Latency';
