<?php
/* Copyright (C) 2014 Nicolas Armando <nicearma@yahoo.com> and Mathieu Millet <htam-net@github.net>
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>. */


$where['sql'] = array();
$where['values'] = array();

if (!empty($vars['device_id'])) {
    $where['sql'][]=" D.device_id = ? ";
    $where['values'][] = $vars['device_id'];
}
if (!empty($vars['vrf_name'])) {
    $where['sql'][]=" VR.vrf_name = ? ";
    $where['values'][] = $vars['vrf_name'];
}

if(isset($vars['results_amount']) && $vars['results_amount'] > 0) {
    $offset = $vars['results'];
} else {
    $offset = 50;
}
if(!isset($vars['page_number']) && $vars['page_number'] < 1) {
    $page_number = 1;
} else {
    $page_number = $vars['page_number'];
}
$start = ($page_number - 1) * $offset;

$where['limit']=" LIMIT $start,$offset"



?>


<form method="post" id="route_form" action="" class="form-inline" role="form">
    <div class="form-group">
        <select name='device_id' id='device_id' class='form-control input-sm'>
            <option value=''>All Devices</option>
            <?php
            if ($_SESSION['userlevel'] >= 5) {
                $results = dbFetchRows("SELECT `device_id`,`hostname` FROM `devices` GROUP BY `hostname` ORDER BY `hostname`");
            } else {
                $results = dbFetchRows("SELECT `D`.`device_id`,`D`.`hostname` FROM `devices` AS `D`, `devices_perms` AS `P` WHERE `P`.`user_id` = ? AND `P`.`device_id` = `D`.`device_id` GROUP BY `hostname` ORDER BY `hostname`", array($_SESSION['user_id']));
            }
            foreach ($results as $data) {
                echo('        <option value="' . $data['device_id'] . '"');
                if ($data['device_id'] == $vars['device_id']) {
                    echo("selected");
                }
                echo(">" . $data['hostname'] . "</option>");
            }

            if ($_SESSION['userlevel'] < 5) {
                $results = dbFetchRows("SELECT `D`.`device_id`,`D`.`hostname` FROM `ports` AS `I` JOIN `devices` AS `D` ON `D`.`device_id`=`I`.`device_id` JOIN `ports_perms` AS `PP` ON `PP`.`port_id`=`I`.`port_id` WHERE `PP`.`user_id` = ? AND `PP`.`port_id` = `I`.`port_id` GROUP BY `hostname` ORDER BY `hostname`", array($_SESSION['user_id']));
            } else {
                $results = array();
            }
            foreach ($results as $data) {
                echo('        <option value="' . $data['device_id'] . '"');
                if ($data['device_id'] == $vars['device_id']) {
                    echo("selected");
                }
                echo(">" . $data['hostname'] . "</option>");
            }
            ?>
        </select>
        <select name="vrf_name" id="vrf_name" class='form-control input-sm'>
            <?php
            if (empty($vars['device_id'])) {
                echo '<option value="">You have to select one device</option>';
            } else {
                echo '<option value="">Select one VRF</option>';
                $results = dbFetchRows("SELECT vrf_name FROM `vrf_lite_cisco` where device_id = ? group by vrf_name ORDER BY vrf_name",array($vars['device_id']));
                foreach ($results as $data) {
                    echo('        <option value="' . $data['vrf_name'] . '"');
                    if ($data['vrf_name'] == $vars['vrf_name']) {
                        echo("selected");
                    }
                    echo(">" . $data['vrf_name'] . "</option>");
                }
            }
            ?>
        </select>
        
        <select name="results" id="results" class="form-control input-sm" onChange="updateResults(this);">');
               <?php   $result_options = array('10','50','100','250','500','1000','5000');
                  foreach($result_options as $option) {
                      echo "<option value='$option'";
                      if($offset == $option) {
                          echo " selected";
                      }
                      echo ">$option</option>";
                  } 
                  ?>
         </select>


    </div>
    <?php
echo ('<input type="hidden" name="page_number" id="page_number" value="'.$page_number.'">
<input type="hidden" name="results_amount" id="results_amount" value="'.$results.'">');
?>
    <button type="submit" class="btn btn-default input-sm">Search</button>
</form>

<?php

echo('<table border="0" cellspacing="0" cellpadding="9" width=100% class="sortable">');

$where["where"]='';
if(!empty($where['sql'])){
  $where["where"].=  " where " . implode(' AND ', $where['sql']) ;
}

$tableRoute = dbFetchRows("select R.*, D.hostname, P.ifName, VR.vrf_name from route R LEFT OUTER JOIN devices D on R.device_id=D.device_id LEFT OUTER JOIN ports P on P.ifIndex=R.ipRouteIfIndex and P.device_id=R.device_id LEFT OUTER JOIN  vrf_lite_cisco VR on VR.device_id=R.device_id and VR.context_name=R.context_name ".$where["where"]." group by D.hostname, VR.vrf_name, R.ipRouteDest ". $where['limit'], $where['values']);
echo ('<tr><th>Hostname</th><th>VRF name</th><th>Ip Destination</th><th>Mask</th><th>Ip Next Hop</th><th>Ports Name</th><th>Metric</th><th>Discovered at</th><th>Type</th><th>Prototype</th><th>RouteIfIndex</th></tr>');
foreach ($tableRoute as $route) {
    echo ('<tr>' .
    '<td>' . $route['hostname'] . '</td>' .
    '<td>' . $route['vrf_name'] . '</td>' .
    '<td>' . $route['ipRouteDest'] . '</td>' .
    '<td>' . $route['ipRouteMask'] . '</td>' .
    '<td>' . $route['ipRouteNextHop'] . '</td>' .
    '<td>' . $route['ifName'] . '</td>' .
    '<td>' . $route['ipRouteMetric'] . '</td>' .
    '<td>' . date('Y-m-d', $route['discoveredAt']) . '</td>' .
    '<td>' . $route['ipRouteType'] . '</td>' .
    '<td>' . $route['ipRouteProto'] . '</td>' .
    '<td>' . $route['ipRouteIfIndex'] . '</td>' .
    '</tr>'
    );
}


unset($where);
unset($tableRoute);




echo("</table>");
if($routing_count['route'] % $offset > 0) {
    echo('<p align="center">'. generate_pagination($routing_count['route'],$offset,$page_number) .'</p>');
}


?>



<script type="text/javascript">
    function updateResults(results) {
       $('#results_amount').val(results.value);
       $('#page_number').val(1);
       $('#route_form').submit();
    }

    function changePage(page,e) {
        e.preventDefault();
        $('#page_number').val(page);
        $('#route_form').submit();
    }
</script>
