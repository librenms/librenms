<?php

/*
* LibreNMS
*
* Copyright (c) 2015 Søren Friis Rosiak <sorenrosiak@gmail.com>
* This program is free software: you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation, either version 3 of the License, or (at your
* option) any later version.  Please see LICENSE.txt at the top level of
* the source code distribution for details.

* Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
*
* This program is free software: you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation, either version 3 of the License, or (at your
* option) any later version.  Please see LICENSE.txt at the top level of
* the source code distribution for details.

*/


if(!isset($vars['section'])) {
    $vars['section'] = "performance";
}

if (empty($vars['dtpickerfrom'])) {
    $vars['dtpickerfrom'] = date($config['dateformat']['byminute'], time() - 3600 * 24 * 2);
}
if (empty($vars['dtpickerto'])) {
    $vars['dtpickerto'] = date($config['dateformat']['byminute']);
}

?>

<hr />
<center>
<form method="post" role="form" id="map" class="form-inline">
    <div class="form-group">
        <label for="dtpickerfrom">From</label>
        <input type="text" class="form-control" id="dtpickerfrom" name="dtpickerfrom" maxlength="16" value="<?php echo $vars['dtpickerfrom']; ?>" data-date-format="YYYY-MM-DD HH:mm">
    </div>
    <div class="form-group">
        <label for="dtpickerto">To</label>
        <input type="text" class="form-control" id="dtpickerto" name="dtpickerto" maxlength=16 value="<?php echo $vars['dtpickerto']; ?>" data-date-format="YYYY-MM-DD HH:mm">
    </div>
    <input type="submit" class="btn btn-default" id="submit" value="Update">
</form>
</center>
<hr />
<script type="text/javascript">
    $(function () {
        $("#dtpickerfrom").datetimepicker({useCurrent: true, sideBySide: true, useStrict: false});
        $("#dtpickerto").datetimepicker({useCurrent: true, sideBySide: true, useStrict: false});
    });
</script>


<?php
if (is_admin() === true || is_read() === true) {
    $query = "SELECT DATE_FORMAT(timestamp, '".$config['alert_graph_date_format']."') Date, xmt,rcv,loss,min,max,avg FROM `device_perf` WHERE `device_id` = ? AND `timestamp` >= ? AND `timestamp` <= ?";
    $param = array($device['device_id'], $vars['dtpickerfrom'], $vars['dtpickerto']);
}
else {
    $query = "SELECT DATE_FORMAT(timestamp, '".$config['alert_graph_date_format']."') Date, xmt,rcv,loss,min,max,avg FROM `device_perf`,`devices_perms` WHERE `device_id` = ? AND alert_log.device_id = devices_perms.device_id AND devices_perms.user_id = ? AND `timestamp` >= ? AND `timestamp` <= ?";
    $param = array($device['device_id'], $_SESSION['user_id'], $vars['dtpickerfrom'], $vars['dtpickerto']);
}

?>

<script src="js/vis.min.js"></script>
<div id="visualization"></div>
<script type="text/javascript">

    var container = document.getElementById('visualization');
    <?php
$groups = array();
$max_val = 0;

foreach(dbFetchRows($query, $param) as $return_value) {
    $date = $return_value['Date'];
    $loss = $return_value['loss'];
    $min = $return_value['min'];
    $max = $return_value['max'];
    $avg = $return_value['avg'];

    if ($max > $max_val) {
        $max_val = $max;
    }

    $data[] = array('x' => $date,'y' => $loss,'group' => 0);
    $data[] = array('x' => $date,'y' => $min,'group' => 1);
    $data[] = array('x' => $date,'y' => $max,'group' => 2);
    $data[] = array('x' => $date,'y' => $avg,'group' => 3);
}

$graph_data = _json_encode($data);
?>
    var names = ['Loss','Min latency','Max latency','Avg latency'];
    var groups = new vis.DataSet();
        groups.add({
            id: 0,
            content: names[0],
            options: {
                drawPoints: {
                    style: 'circle'
                },
                shaded: {
                    orientation: 'bottom'
                }
            }
        });

        groups.add({
            id: 1,
            content: names[1],
            options: {
                yAxisOrientation: 'right',
                drawPoints: {
                    style: 'circle'
                },
                shaded: {
                    orientation: 'bottom'
                }
            }
        });

        groups.add({
            id: 2,
            content: names[2],
            options: {
                yAxisOrientation: 'right',
                drawPoints: {
                    style: 'circle'
                },
                shaded: {
                    orientation: 'bottom'
                }
            }
        });

        groups.add({
            id: 3,
            content: names[3],
            options: {
                yAxisOrientation: 'right',
                drawPoints: {
                    style: 'circle'
                },
                shaded: {
                    orientation: 'bottom'
                }
            }
        });
<?php

?>

    var items =
        <?php
echo $graph_data; ?>
    ;
    var dataset = new vis.DataSet(items);
    var options = {
        barChart: {width:50, align:'right',handleOverlap:'sideBySide'}, // align: left, center, right
        drawPoints: false,
        legend: {left:{position:"bottom-left"}},
        dataAxis: {
            icons:true,
            showMajorLabels: true,
            showMinorLabels: true,
            customRange: {
               left: {
                    min: 0, max: 100
               },
               right: {
                    min: 0, max: <?php echo $max_val; ?>
                }
            }
        },
        zoomMin: 86400, //24hrs
        zoomMax: <?php
$first_date = reset($data);
$last_date = end($data);
$milisec_diff = abs(strtotime($first_date[x]) - strtotime($last_date[x])) * 1000;
echo $milisec_diff;
?>,
        orientation:'top'
    };
    var graph2d = new vis.Graph2d(container, items, groups, options);

</script>
