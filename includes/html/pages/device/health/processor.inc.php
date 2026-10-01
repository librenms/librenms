<?php

$i = '1';

$processors = dbFetchRows('SELECT * FROM `processors` WHERE device_id = ?', [$device['device_id']]);

foreach ($processors as $proc) {
    $proc_url = 'graphs/id=' . $proc['processor_id'] . '/type=processor_usage/';
    $base_url = route('graph', ['type' => 'processor_usage', 'id' => $proc['processor_id'], 'from' => '-1d']);
    $mini_url = $base_url . '&amp;width=80&amp;height=20&amp;bg=f4f4f4';

    $text_descr = rewrite_entity_descr($proc['processor_descr']);

    $percent = round($proc['processor_usage']);

    $graph_array['id'] = $proc['processor_id'];
    $graph_array['type'] = 'processor_usage';

    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>$text_descr <div class='pull-right'>$percent% used</div></h3>
            </div>";
    echo "<div class='panel-body'>";
    include 'includes/html/print-graphrow.inc.php';
    echo '</div></div>';
}//end foreach
