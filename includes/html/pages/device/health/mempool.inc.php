<?php

$graph_type = 'mempool_usage';

$i = '1';

if (count_mib_mempools($device) > 0) {
    $mempools = get_mib_mempools($device);
    $graph_type = 'device_mempool';
} else {
    $mempools = dbFetchRows('SELECT * FROM `mempools` WHERE device_id = ?', array($device['device_id']));
}

// FIXME css alternating colours
foreach ($mempools as $mempool) {
    if (!is_integer($i / 2)) {
        $row_colour = \LibreNMS\Config::get('list_colour.even');
    } else {
        $row_colour = \LibreNMS\Config::get('list_colour.odd');
    }

    $text_descr = rewrite_entity_descr($mempool['mempool_descr']);

    if ($graph_type == 'device_mempool') {
        $id = 'device';
        $val = $device['device_id'];
    } else {
        $id = 'id';
        $val = $mempool['mempool_id'];
    }
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
