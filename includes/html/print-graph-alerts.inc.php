<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * Copyright (c) 2015 Søren Friis Rosiak <sorenrosiak@gmail.com>
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       https://www.librenms.org
 * @copyright  2017 LibreNMS
 * @author     LibreNMS Contributors
*/

$pagetitle[] = 'Alert Stats';
$param = [];
$sql = '';
if (isset($device['device_id']) && $device['device_id'] > 0) {
    $sql = ' AND alert_log.device_id=?';
    $param = [
        $device['device_id'],
    ];
}

if (! Auth::user()->hasGlobalRead()) {
    $device_ids = Permissions::devicesForUser()->toArray() ?: [0];
    $sql .= ' AND `alert_log`.`device_id` IN ' . dbGenPlaceholders(count($device_ids));
    $param = array_merge($param, $device_ids);
}

$query = "SELECT DATE_FORMAT(time_logged, '" . \LibreNMS\Config::get('alert_graph_date_format') . "') Date, COUNT(alert_log.rule_id) totalCount, alert_rules.severity Severity FROM alert_log,alert_rules WHERE alert_log.rule_id=alert_rules.id AND `alert_log`.`state` != 0 $sql GROUP BY DATE_FORMAT(time_logged, '" . \LibreNMS\Config::get('alert_graph_date_format') . "'),alert_rules.severity";

?>
<br>
<div class="panel panel-default">
    <div class="panel-heading">
        Device alerts
    </div>
    <br>
    <div style="margin:0 auto;width:99%;">

<script src="js/vis.min.js"></script>
<div id="visualization" style="margin-bottom: -120px;"></div>
<script type="text/javascript">

    var container = document.getElementById('visualization');
    <?php
    $groups = [];
    $max_count = 0;
    $data = [];

    foreach (dbFetchRows($query, $param) as $return_value) {
        $date = $return_value['Date'];
        $count = $return_value['totalCount'];
        if ($count > $max_count) {
            $max_count = $count;
        }

        $severity = $return_value['Severity'];
        $data[] = [
            'x' => $date,
            'y' => $count,
            'group' => $severity,
        ];
        if (! in_array($severity, $groups)) {
            array_push($groups, $severity);
        }
    }

    $graph_data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    ?>
    var groups = new vis.DataSet();
<?php

foreach ($groups as $group) {
    echo "groups.add({id: '$group', content: '$group' })\n";
}

?>

    var items =
        <?php
        echo $graph_data; ?>
    ;
    var dataset = new vis.DataSet(items);
    var options = {
        style:'bar',
        barChart: { width:50, align:'right', sideBySide:true}, // align: left, center, right
        drawPoints: false,
        legend: {left:{position:"bottom-left"}},
        dataAxis: {
            icons:true,
            showMajorLabels: true,
            showMinorLabels: true,
        },
        zoomMin: 86400, //24hrs
        zoomMax: <?php
        $first_date = reset($data);
        $last_date = end($data);
        $milisec_diff = abs(strtotime($first_date['x']) - strtotime($last_date['x'])) * 1000;
        echo $milisec_diff;
        ?>,
        orientation:'top'
    };
    var graph2d = new vis.Graph2d(container, items, groups, options);

</script>
