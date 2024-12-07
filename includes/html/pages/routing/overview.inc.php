<?php

foreach ($datas as $type) {
    $type = basename($type);
    if ($type != 'overview') {
        if (is_file("includes/html/pages/routing/overview/$type.inc.php")) {
            $g_i++;
            if (! is_integer($g_i / 2)) {
                $row_colour = \LibreNMS\Config::get('list_colour.even');
            } else {
                $row_colour = \LibreNMS\Config::get('list_colour.odd');
            }

            echo '<div style="background-color: ' . $row_colour . ';">';
            echo '<div style="padding:4px 0px 0px 8px;"><span class=graphhead>' . $type_text[$type] . '</span>';

            include "includes/html/pages/routing/overview/$type.inc.php";

            echo '</div>';
            echo '</div>';
        } else {
            $graph_title = $type_text[$type];
            $graph_type = 'device_' . $type;

            include 'includes/html/print-device-graph.php';
        }
    }
}
