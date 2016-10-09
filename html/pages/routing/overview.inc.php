<?php

foreach ($datas as $type) {
    if ($type != 'overview') {
        if (is_file('pages/routing/overview/'.mres($type).'.inc.php')) {
            $g_i++;
            if (!is_integer($g_i / 2)) {
                $row_colour = $list_colour_a;
            } else {
                $row_colour = $list_colour_b;
            }

            echo '<div style="background-color: '.$row_colour.';">';
            echo '<div style="padding:4px 0px 0px 8px;"><span class=graphhead>'.$type_text[$type].'</span>';

            include 'pages/routing/overview/'.mres($type).'.inc.php';

            echo '</div>';
            echo '</div>';
        } else {
            $graph_title = $type_text[$type];
            $graph_type  = 'device_'.$type;

            include 'includes/print-device-graph.php';
        }
    }
}
