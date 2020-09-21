<?php

$i = '1';

$processors = dbFetchRows('SELECT * FROM `processors` WHERE device_id = ?', [$device['device_id']]);

foreach ($processors as $proc) {
    $id = 'id';
    $val = $proc['processor_id'];
    $proc_url = 'graphs/' . $id . '=' . $val . '/type=processor_usage/';
    $base_url = 'graph.php?' . $id . '=' . $val . '&amp;type=processor_usage&amp;from=' . \LibreNMS\Config::get('time.day') . '&amp;to=' . \LibreNMS\Config::get('time.now');
    $mini_url = $base_url . '&amp;width=80&amp;height=20&amp;bg=f4f4f4';

    $text_descr = rewrite_entity_descr($proc['processor_descr']);

    $proc_popup = "onmouseover=\"return overlib('<div class=list-large>" . $device['hostname'] . ' - ' . $text_descr;
    $proc_popup .= "</div><img src=\'" . $base_url . "&amp;width=400&amp;height=125\'>";
    $proc_popup .= "', RIGHT" . \LibreNMS\Config::get('overlib_defaults') . ');" onmouseout="return nd();"';
    $percent = round($proc['processor_usage']);

    $graph_array[$id] = $val;
    $graph_array['type'] = 'processor_usage';

    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>$text_descr <div class='pull-right'>$percent% used</div></h3>
            </div>";
    echo "<div class='panel-body'>";
    include 'includes/html/print-graphrow.inc.php';
    echo '</div></div>';
}//end foreach
