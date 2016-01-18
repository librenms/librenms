<?php

$graph_type = 'processor_usage';

$i = '1';

if (count_mib_processors($device) > 0) {
    $processors = get_mib_processors($device);
    $graph_type = 'device_processor';
}
else {
    $processors = dbFetchRows('SELECT * FROM `processors` WHERE device_id = ?', array($device['device_id']));
}

foreach ($processors as $proc) {
    if ($graph_type == 'device_processor') {
        $id = 'device';
        $val = $device['device_id'];
    }
    else {
        $id = 'id';
        $val = $proc['processor_id'];
    }
    $proc_url = 'graphs/'.$id.'='.$val.'/type='.$graph_type.'/';
    $base_url = 'graph.php?'.$id.'='.$val.'&amp;type='.$graph_type.'&amp;from='.$config['time']['day'].'&amp;to='.$config['time']['now'];
    $mini_url = $base_url.'&amp;width=80&amp;height=20&amp;bg=f4f4f4';

    $text_descr = rewrite_entity_descr($proc['processor_descr']);

    $proc_popup  = "onmouseover=\"return overlib('<div class=list-large>".$device['hostname'].' - '.$text_descr;
    $proc_popup .= "</div><img src=\'".$base_url."&amp;width=400&amp;height=125\'>";
    $proc_popup .= "', RIGHT".$config['overlib_defaults'].');" onmouseout="return nd();"';
    $percent = round($proc['processor_usage']);

    $graph_array[$id] = $val;
    $graph_array['type'] = $graph_type;

    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>$text_descr <div class='pull-right'>$percent% used</div></h3>
            </div>";
    echo "<div class='panel-body'>";
    include 'includes/print-graphrow.inc.php';
    echo "</div></div>";
}//end foreach
