<?php

$graph_type = 'mempool_usage';

$i = '1';

// FIXME css alternating colours
foreach (dbFetchRows('SELECT * FROM `mempools` WHERE device_id = ?', array($device['device_id'])) as $mempool) {
    if (!is_integer($i / 2)) {
        $row_colour = $list_colour_a;
    }
    else {
        $row_colour = $list_colour_b;
    }

    $text_descr = rewrite_entity_descr($mempool['mempool_descr']);

    $mempool_url = 'graphs/id='.$mempool['mempool_id'].'/type=mempool_usage/';
    $mini_url    = 'graph.php?id='.$mempool['mempool_id'].'&amp;type='.$graph_type.'&amp;from='.$config['time']['day'].'&amp;to='.$config['time']['now'].'&amp;width=80&amp;height=20&amp;bg=f4f4f4';

    $mempool_popup  = "onmouseover=\"return overlib('<div class=list-large>".$device['hostname'].' - '.$text_descr;
    $mempool_popup .= "</div><img src=\'graph.php?id=".$mempool['mempool_id'].'&amp;type='.$graph_type.'&amp;from='.$config['time']['month'].'&amp;to='.$config['time']['now']."&amp;width=400&amp;height=125\'>";
    $mempool_popup .= "', RIGHT".$config['overlib_defaults'].');" onmouseout="return nd();"';

    $total = formatStorage($mempool['mempool_total']);
    $used  = formatStorage($mempool['mempool_used']);
    $free  = formatStorage($mempool['mempool_free']);

    $perc = round(($mempool['mempool_used'] / $mempool['mempool_total'] * 100));

    $background       = get_percentage_colours($percent);
    $right_background = $background['right'];
    $left_background  = $background['left'];

    $graph_array['id']   = $mempool['mempool_id'];
    $graph_array['type'] = $graph_type;

    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>$text_descr <div class='pull-right'>$used/$total - $perc% used</div></h3>
            </div>";
    echo "<div class='panel-body'>";
    include 'includes/print-graphrow.inc.php';
    echo "</div></div>";

    $i++;
}//end foreach
