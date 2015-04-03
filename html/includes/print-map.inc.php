<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$tmp_devices = array();
if (!empty($device['hostname'])) {
    $sql = ' WHERE `devices`.`hostname`=?';
    $sql_array = array($device['hostname']);
} else {
    $sql = ' WHERE 1';
}

$sql .= ' AND `local_device_id` != 0';

$tmp_ids = array();
$tmp_host = array();
foreach (dbFetchRows("SELECT DISTINCT least(`devices`.`device_id`, `remote_device_id`) AS `remote_device_id`, GREATEST(`remote_device_id`,`devices`.`device_id`) AS `local_device_id` FROM `links` LEFT JOIN `ports` ON `local_port_id`=`ports`.`port_id` LEFT JOIN `devices` ON `ports`.`device_id`=`devices`.`device_id` $sql", $sql_array) as $link_devices) {
    if (!in_array($link_devices['local_device_id'], $tmp_ids)) {
        $link_dev = dbFetchRow("SELECT `hostname`,`location` FROM `devices` WHERE `device_id`=?",array($link_devices['local_device_id']));
        $tmp_devices[] = array('id'=>$link_devices['local_device_id'],'label'=>$link_dev['hostname'],'title'=>$link_dev['hostname'],'group'=>$link_dev['location']);
    }
    if (!in_array($link_devices['remote_device_id'], $tmp_ids) && $link_devices['remote_device_id'] > 0) {
        $link_dev = dbFetchRow("SELECT `hostname`,`location` FROM `devices` WHERE `device_id`=?",array($link_devices['remote_device_id']));
        $tmp_devices[] = array('id'=>$link_devices['remote_device_id'],'label'=>$link_dev['hostname'],'title'=>$link_dev['hostname'],'group'=>$link_dev['location']);
    }
    array_push($tmp_ids,$link_devices['local_device_id']);
    array_push($tmp_ids,$link_devices['remote_device_id']);
}
foreach (dbFetchRows("SELECT DISTINCT `remote_hostname` AS `hostname` FROM `links` WHERE `remote_device_id`= 0") as $link_devices) {
    $tmp_devices[] = array('id'=>md5($link_devices['hostname']),'label'=>$link_devices['hostname'],'title'=>$link_devices['hostname'],'group'=>'');
    array_push($tmp_host,$link_devices['hostname']);
}

$tmp_ids = implode(',',$tmp_ids);
$tmp_host = "'".implode("','",$tmp_host)."'";
 
$nodes = json_encode($tmp_devices);
 
if (is_array($tmp_devices[0])) {
    $tmp_links = array();
    foreach (dbFetchRows("SELECT local_device_id, remote_device_id, `remote_hostname`,`ports`.`ifName` AS `local_port`, `remote_port`,`ports`.`ifSpeed` AS ifSpeed FROM `links` LEFT JOIN `ports` ON `local_port_id`=`ports`.`port_id` LEFT JOIN `devices` ON `ports`.`device_id`=`devices`.`device_id` WHERE (`local_device_id` IN ($tmp_ids) AND (`remote_device_id` IN ($tmp_ids) OR `remote_hostname` IN ($tmp_host)))") as $link_devices) {
        foreach ($tmp_devices as $k=>$v) {
            if ($v['id'] == $link_devices['local_device_id']) {
                $from = $v['id'];
                $port = $link_devices['local_port'];
            }
            if ($v['id'] == $link_devices['remote_device_id'] || $v['id'] == md5($link_devices['remote_hostname'])) {
                $to = $v['id'];
                $port .= ' > ' .$link_devices['remote_port'];
            }
        }
        $speed = $link_devices['ifSpeed']/1000/1000;
        if ($speed == 100) {
            $width = 3;
        } elseif ($speed == 1000) {
            $width = 5;
        } elseif ($speed == 10000) {
            $width = 10;
        } elseif ($speed == 40000) {
            $width = 15;
        } elseif ($speed == 100000) {
            $width = 20;
        } else {
            $width = 1;
        }
        $tmp_links[] = array('from'=>$from,'to'=>$to,'label'=>$port,'title'=>$port,'width'=>$width);
    }
 
    $edges = json_encode($tmp_links);
 
?>
 
<div id="visualization"></div>
<script src="js/vis.min.js"></script>
<script type="text/javascript">

    // create an array with nodes
    var nodes =
<?php
echo $nodes;
?>
    ;
 
    // create an array with edges
    var edges =
<?php
echo $edges;
?>
    ;
 
    // create a network
    var container = document.getElementById('visualization');
    var data = {
            nodes: nodes,
        edges: edges,
        stabilize: true
    };
    var options = {physics: {barnesHut: {gravitationalConstant: -11900, centralGravity: 1.4, springLength: 203, springConstant: 0.05, damping: 0.3}}, smoothCurves: false};
    var network = new vis.Network(container, data, options);
    network.on("resize", function(params) {console.log(params.width,params.height)});
    network.on('click', function (properties) {
        if (properties.nodes > 0) {
            window.location.href = "/device/device="+properties.nodes+"/tab=map/"
        }
    });
</script>

<?php

}

$pagetitle[] = "Map";
?>
