<?php
/*
 * LibreNMS module to capture statistics from the CISCO-NTP-MIB
 *
 * Copyright (c) 2016 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

require_once "../includes/component.php";
$component = new component();
$options = array();
$options['filter']['ignore'] = array('=',0);
$options['type'] = 'ntp';
$components = $component->getComponents(null,$options);

print_optionbar_start();

$view_options = array(
    'all'       => 'All',
    'error'     => 'Error',
);
if (!$vars['view']) {
    $vars['view'] = 'all';
}

$graph_options = array(
    'none'          => 'No Graphs',
    'stratum'       => 'Stratum',
    'offset'        => 'Offset',
    'delay'         => 'Delay',
    'dispersion'    => 'Dispersion',
);
if (!$vars['graph']) {
    $vars['graph'] = 'none';
}

echo '<span style="font-weight: bold;">NTP Peers</span> &#187; ';

// The menu option - on the left
$sep = '';
foreach ($view_options as $option => $text) {
    if (empty($vars['view'])) {
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

// The status option - on the right
echo '<div class="pull-right">';
$sep = '';
foreach ($graph_options as $option => $text) {
    if (empty($vars['graph'])) {
        $vars['graph'] = $option;
    }
    echo $sep;
    if ($vars['graph'] == $option) {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link($text, $vars, array('graph' => $option));
    if ($vars['graph'] == $option) {
        echo '</span>';
    }
    $sep = ' | ';
}
unset($sep);
echo '</div>';
print_optionbar_end();

?>
<table id='table' class='table table-condensed table-responsive table-striped'>
    <thead>
    <tr>
        <th>Device</th>
        <th>Peer</th>
        <th>Stratum</th>
        <th>Error</th>
    </tr>
    </thead>
<?php
    $count = 0;
    // Loop through each device in the componenet array
    foreach ($components as $devid => $comp) {
        $device = device_by_id_cache($devid);

        // Loop through each component
        foreach ($comp as $compid => $array) {
            $display = true;
            if ($vars['view'] == 'error') {
                // Only display peers with errors
                if ($array['status'] != 2) {
                    $display = false;
                }
            }
            if ($array['status'] == 2) {
                $status = 'class="danger"';
            } else {
                $status = '';
            }

            if ($display === true) {
                $link = generate_device_link($device,null,array('tab' => 'apps', 'app' => 'ntp'));
                $count++;
?>
    <tr <?php echo $status; ?>>
        <td><?php echo $link; ?></td>
        <td><?php echo $array['peer']; ?></td>
        <td><?php echo $array['stratum']; ?></td>
        <td><?php echo $array['error']; ?></td>
    </tr>
<?php
                $graph_array = array();
                $graph_array['device'] = $device['device_id'];
                $graph_array['height'] = '100';
                $graph_array['width']  = '215';
                $graph_array['to']     = $config['time']['now'];

                // Which graph type do we want?
                if ($vars['graph'] == "stratum") {
                    $graph_array['type']   = 'device_ntp_stratum';
                } elseif ($vars['graph'] == "offset") {
                    $graph_array['type']   = 'device_ntp_offset';
                } elseif ($vars['graph'] == "delay") {
                    $graph_array['type']   = 'device_ntp_delay';
                } elseif ($vars['graph'] == "dispersion") {
                    $graph_array['type']   = 'device_ntp_dispersion';
                } else {
                    // No Graph
                    unset($graph_array);
                }

                // Do we want a graph.
                if (is_array($graph_array)) {
                    echo '<tr>';
                    echo '<td colspan="4">';
                    require 'includes/print-graphrow.inc.php';
                    echo '</td>';
                    echo '</tr>';
                }

            } // End if display
        } // End foreach component
    } // End foreach device

    // If there are no results, let the user know.
    if ($count == 0) {
?>
        <tr>
            <td colspan="4" align="center">No Matching NTP Peers</td>
        </tr>
<?php
    }
?>
</table>
