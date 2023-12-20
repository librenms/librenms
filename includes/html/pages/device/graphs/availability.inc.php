<?php

$graph_type = 'availability';

$deviceModel = DeviceCache::getPrimary();
foreach ($deviceModel->availability as $index => $duration) {
    if (is_integer($index / 2)) {
        $row_colour = \LibreNMS\Config::get('list_colour.even');
    } else {
        $row_colour = \LibreNMS\Config::get('list_colour.odd');
    }

    $graph_array['device'] = $duration->device_id;
    $graph_array['type'] = 'device_' . $graph_type;
    $graph_array['duration'] = $duration->duration;

    $human_duration = \LibreNMS\Util\Time::formatInterval($duration->duration, parts: 1);
    $graph_title = $deviceModel->displayName() . ' - ' . $human_duration;

    echo "<div class='panel panel-default'>
            <div class='panel-heading'>
                <h3 class='panel-title'>" . $human_duration . "<div class='pull-right'>" . round($duration->availability_perc, 3) . '%</div></h3>
            </div>';
    echo "<div class='panel-body'>";
    include 'includes/html/print-graphrow.inc.php';
    echo '</div></div>';
}
