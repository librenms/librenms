<?php

/*
 * Copyright (C) 2099  Bruno Prémont <bonbons AT linux-vserver.org>
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation; only version 2 of the License is applicable.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA  02150-1301, USA.
 */

require 'includes/html/collectd/config.php';
require 'includes/html/collectd/functions.php';
require 'includes/html/collectd/definitions.php';

global $MetaGraphDefs;
load_graph_definitions();

print_optionbar_start();

$link_array = [
    'page' => 'device',
    'device' => $device['device_id'],
    'tab' => 'collectd',
];

$plugins = collectd_list_plugins($device['hostname']);
unset($sep);
foreach ($plugins as &$plugin) {
    if (! $vars['plugin']) {
        $vars['plugin'] = $plugin;
    }

    echo $sep;
    if ($vars['plugin'] == $plugin) {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link(htmlspecialchars((string) $plugin), $link_array, ['plugin' => $plugin]);
    if ($vars['plugin'] == $plugin) {
        echo '</span>';
    }

    $sep = ' | ';
}

unset($sep);

print_optionbar_end();

$i = 0;

$pinsts = collectd_list_pinsts($device['hostname'], $vars['plugin']);
foreach ($pinsts as &$instance) {
    $types = collectd_list_types($device['hostname'], $vars['plugin'], $instance);
    foreach ($types as &$type) {
        $typeinstances = collectd_list_tinsts($device['hostname'], $vars['plugin'], $instance, $type);

        if ($MetaGraphDefs[$type]) {
            $typeinstances = [''];
        }

        foreach ($typeinstances as &$tinst) {
            $i++;
            if (! is_int($i / 2)) {
                $row_colour = \App\Facades\LibrenmsConfig::get('list_colour.even');
            } else {
                $row_colour = \App\Facades\LibrenmsConfig::get('list_colour.odd');
            }

            echo '<div style="background-color: ' . $row_colour . ';">';
            echo '<div class="graphhead" style="padding:4px 0px 0px 8px;">';
            if ($tinst) {
                echo $vars['plugin'] . " $instance - $type - $tinst";
            } else {
                echo $vars['plugin'] . " $instance - $type";
            }

            echo '</div>';

            $graph_array['type'] = 'device_collectd';
            $graph_array['device'] = $device['device_id'];

            $graph_array['c_plugin'] = $vars['plugin'];
            $graph_array['c_plugin_instance'] = $instance;
            $graph_array['c_type'] = $type;
            $graph_array['c_type_instance'] = $tinst;

            include 'includes/html/print-graphrow.inc.php';

            echo '</div>';
        }
    }
}

$pagetitle[] = 'CollectD';
