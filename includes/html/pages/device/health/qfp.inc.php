<?php

$component = new \LibreNMS\Component();
$components = $component->getComponents($device['device_id'], array('type' => 'cisco-qfp'));
$components = $components[$device['device_id']];

foreach ($components as $component_id => $component) {
    
    $default_graph_array = array(
        'from' => \LibreNMS\Config::get('time.day'),
        'to' => \LibreNMS\Config::get('time.now'),
        'id' => $component_id,
        'page' => 'graphs'
    );

    /*
     * Main container for QFP
     */
    $text_descr = $component['name'];
    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h2 class='panel-title'><b>$text_descr</b></h2>
            </div>";
    echo "<div class='panel-body'>";



    /*
     * QFP Utilization (Load)
     */
    $graph_array = $default_graph_array;
    $graph_array['type'] = 'qfp_util';
    $text_descr = 'QFP Utilizatoin';
    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>$text_descr</h3>
            </div>";
    echo "<div class='panel-body'>";
    include 'includes/html/print-graphrow.inc.php';
    echo "</div></div>";

    /*
     * Relative QFP utilization to packets processed
     */
    $graph_array = $default_graph_array;
    $graph_array['type'] = 'qfp_relativeutil';
    $text_descr = 'QFP Relative utilizatoin per pps';
    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>$text_descr</h3>
            </div>";
    echo "<div class='panel-body'>";
    include 'includes/html/print-graphrow.inc.php';
    echo "</div></div>";

    /*
     * QFP Packets In/Out
     */
    $graph_array = $default_graph_array;
    $graph_array['type'] = 'qfp_packets';
    $text_descr = 'QFP Throughput and packets';
    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>$text_descr</h3>
            </div>";
    echo "<div class='panel-body'>";
    include 'includes/html/print-graphrow.inc.php';
    echo "</div></div>";


    /*
     * QFP Throughput In/Out
     */
    $graph_array = $default_graph_array;
    $graph_array['type'] = 'qfp_throughput';
    $text_descr = 'QFP Throughput and packets';
    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>$text_descr</h3>
            </div>";
    echo "<div class='panel-body'>";
    include 'includes/html/print-graphrow.inc.php';
    echo "</div></div>";


    /*
     * QFP Memory resources
     */
    $graph_array = $default_graph_array;
    $graph_array['type'] = 'qfp_memory';
    $text_descr = 'QFP Memory';
    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>$text_descr</h3>
            </div>";
    echo "<div class='panel-body'>";
    include 'includes/html/print-graphrow.inc.php';
    echo "</div></div>";
    echo "</div></div>";


}

/*

// FIXME css alternating colours
foreach ($mempools as $mempool) {
    $text_descr = rewrite_entity_descr($mempool['mempool_descr']);

    $mempool_url = 'graphs/'.$id.'='.$val.'/type='.$graph_type.'/';
    $mini_url = 'graph.php?' . $id . '=' . $val . '&amp;type=' . $graph_type . '&amp;from=' . \LibreNMS\Config::get('time.day') . '&amp;to=' . \LibreNMS\Config::get('time.now') . '&amp;width=80&amp;height=20&amp;bg=f4f4f4';

    $mempool_popup  = "onmouseover=\"return overlib('<div class=list-large>".$device['hostname'].' - '.$text_descr;
    $mempool_popup .= "</div><img src=\'graph.php?'.$id.'=" . $val . '&amp;type=' . $graph_type . '&amp;from=' . \LibreNMS\Config::get('time.month') . '&amp;to=' . \LibreNMS\Config::get('time.now') . "&amp;width=400&amp;height=125\'>";
    $mempool_popup .= "', RIGHT" . \LibreNMS\Config::get('overlib_defaults') . ');" onmouseout="return nd();"';

    $total = formatStorage($mempool['mempool_total']);
    $used  = formatStorage($mempool['mempool_used']);
    $free  = formatStorage($mempool['mempool_free']);

    // don't bother recalculating if mempools use percentage
    if ($mempool['percentage'] === true) {
        $perc = round($mempool['mempool_used']);
    } else {
        $perc = round(($mempool['mempool_used'] / $mempool['mempool_total'] * 100));
    }

    $background       = get_percentage_colours($percent);
    $right_background = $background['right'];
    $left_background  = $background['left'];

    $graph_array[$id] = $val;
    $graph_array['type'] = $graph_type;

    echo "<div class='panel panel-default'>
            <div class='panel-heading'>";
    if ($mempool['percentage'] === true) {
        echo "                <h3 class='panel-title'>$text_descr <div class='pull-right'>$perc% used</div></h3>";
    } else {
        echo "                <h3 class='panel-title'>$text_descr <div class='pull-right'>$used/$total - $perc% used</div></h3>";
    }
    echo "            </div>";
    echo "<div class='panel-body'>";
    include 'includes/html/print-graphrow.inc.php';
    echo "</div></div>";

    $i++;
}//end foreach

*/
