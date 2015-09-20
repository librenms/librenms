<?php

$graph_type = 'processor_usage';

$i = '1';
foreach (dbFetchRows('SELECT * FROM `processors` WHERE device_id = ?', array($device['device_id'])) as $proc) {
    $proc_url = 'graphs/id='.$proc['processor_id'].'/type=processor_usage/';

    $mini_url = 'graph.php?id='.$proc['processor_id'].'&amp;type='.$graph_type.'&amp;from='.$config['time']['day'].'&amp;to='.$config['time']['now'].'&amp;width=80&amp;height=20&amp;bg=f4f4f4';

    $text_descr = $proc['processor_descr'];

    $text_descr = rewrite_entity_descr($text_descr);

    $proc_popup  = "onmouseover=\"return overlib('<div class=list-large>".$device['hostname'].' - '.$text_descr;
    $proc_popup .= "</div><img src=\'graph.php?id=".$proc['processor_id'].'&amp;type='.$graph_type.'&amp;from='.$config['time']['month'].'&amp;to='.$config['time']['now']."&amp;width=400&amp;height=125\'>";
    $proc_popup .= "', RIGHT".$config['overlib_defaults'].');" onmouseout="return nd();"';
    $percent = round($proc['processor_usage']);

    $graph_array['id']   = $proc['processor_id'];
    $graph_array['type'] = $graph_type;

    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>$text_descr <div class='pull-right'>$percent% used</div></h3>
            </div>";
    echo "<div class='panel-body'>";
    include 'includes/print-graphrow.inc.php';
    echo "</div></div>";
}//end foreach