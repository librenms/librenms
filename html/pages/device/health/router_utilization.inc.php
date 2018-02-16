<?php

$graph_type = 'routerutilization_usage';

$row = 1;

foreach (dbFetchRows('SELECT * FROM `router_utilization` WHERE device_id = ? AND forwarding_element = ? ORDER BY `resource`', array($device['device_id'], null)) as $entry) {
    if (is_integer($row / 2)) {
        $row_colour = $list_colour_a;
    } else {
        $row_colour = $list_colour_b;
    }

    if ($entry['feature'] == '') {
        $panel_title = $entry['resource'];
    } else {
        $panel_title = $entry['resource'] . " (" . $entry['feature'] . ")";
    }

    $ru_url = 'graphs/id='.$entry['id'].'/type='.$graph_type.'_'.$entry['feature'].'/';

    $graph_array['id']   = $entry['id'];
    $graph_array['type'] = $graph_type;

    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>$panel_title</h3>
            </div>";
    echo "<div class='panel-body'>";
    include 'includes/print-graphrow.inc.php';
    echo "</div></div>";

    $row++;
}//end foreach
