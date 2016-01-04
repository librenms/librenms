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

//Don't know where this should come from, but it is used later, so I just define it here.
$row_colour="#ffffff";

$sql_array= array();
if (!empty($device['hostname'])) {
    $sql = ' AND (`D1`.`hostname`=? OR `D2`.`hostname`=?)';
    $sql_array = array($device['hostname'], $device['hostname']);
    $mac_sql = ' AND `D`.`hostname` = ?';
    $mac_array = array($device['hostname']);
}
else {
    $sql = ' ';
}

if (is_admin() === false && is_read() === false) {
    $join_sql    .= ' LEFT JOIN `devices_perms` AS `DP` ON `D1`.`device_id` = `DP`.`device_id`';
    $sql  .= ' AND `DP`.`user_id`=?';
    $sql_array[] = $_SESSION['user_id'];
}

$tmp_devices = array();
$tmp_ids = array();
$tmp_links = array();
$tmp_link_ids = array();
$ports = array();
$devices = array();

if (in_array('mac',$config['network_map_items'])) {
    $ports = dbFetchRows("SELECT
                             `D1`.`device_id` AS `local_device_id`,
                             `D1`.`os` AS `local_os`,
                             `D1`.`hostname` AS `local_hostname`,
                             `D2`.`device_id` AS `remote_device_id`,
                             `D2`.`os` AS `remote_os`,
                             `D2`.`hostname` AS `remote_hostname`,
                             `P1`.`port_id` AS `local_port_id`,
                             `P1`.`device_id` AS `local_port_device_id`,
                             `P1`.`ifName` AS `local_ifname`,
                             `P1`.`ifSpeed` AS `local_ifspeed`,
                             `P1`.`ifOperStatus` AS `local_ifoperstatus`,
                             `P1`.`ifAdminStatus` AS `local_ifadminstatus`,
                             `P1`.`ifInOctets_rate` AS `local_ifinoctets_rate`,
                             `P1`.`ifOutOctets_rate` AS `local_ifoutoctets_rate`,
                             `P2`.`port_id` AS `remote_port_id`,
                             `P2`.`device_id` AS `remote_port_device_id`,
                             `P2`.`ifName` AS `remote_ifname`,
                             `P2`.`ifSpeed` AS `remote_ifspeed`,
                             `P2`.`ifOperStatus` AS `remote_ifoperstatus`,
                             `P2`.`ifAdminStatus` AS `remote_ifadminstatus`,
                             `P2`.`ifInOctets_rate` AS `remote_ifinoctets_rate`,
                             `P2`.`ifOutOctets_rate` AS `remote_ifoutoctets_rate`
                      FROM `ipv4_mac` AS `M`
                             LEFT JOIN `ports` AS `P1` ON `P1`.`port_id`=`M`.`port_id`
                             LEFT JOIN `ports` AS `P2` ON `P2`.`ifPhysAddress`=`M`.`mac_address`
                             LEFT JOIN `devices` AS `D1` ON `P1`.`device_id`=`D1`.`device_id`
                             LEFT JOIN `devices` AS `D2` ON `P2`.`device_id`=`D2`.`device_id`
                             $join_sql
                      WHERE
                             `M`.`mac_address` NOT IN ('000000000000','ffffffffffff') AND
                             `P1`.`port_id` IS NOT NULL AND
                             `P2`.`port_id` IS NOT NULL AND
                             `D1`.`device_id` != `D2`.`device_id`
                             $sql
                      GROUP BY `P1`.`port_id`,`P2`.`port_id`
                     ", $sql_array);
}

if (in_array('xdp', $config['network_map_items'])) {
    $devices = dbFetchRows("SELECT
                             `D1`.`device_id` AS `local_device_id`,
                             `D1`.`os` AS `local_os`,
                             `D1`.`hostname` AS `local_hostname`,
                             `D2`.`device_id` AS `remote_device_id`,
                             `D2`.`os` AS `remote_os`,
                             `D2`.`hostname` AS `remote_hostname`,
                             `P1`.`port_id` AS `local_port_id`,
                             `P1`.`device_id` AS `local_port_device_id`,
                             `P1`.`ifName` AS `local_ifname`,
                             `P1`.`ifSpeed` AS `local_ifspeed`,
                             `P1`.`ifOperStatus` AS `local_ifoperstatus`,
                             `P1`.`ifAdminStatus` AS `local_ifadminstatus`,
                             `P1`.`ifInOctets_rate` AS `local_ifinoctets_rate`,
                             `P1`.`ifOutOctets_rate` AS `local_ifoutoctets_rate`,
                             `P2`.`port_id` AS `remote_port_id`,
                             `P2`.`device_id` AS `remote_port_device_id`,
                             `P2`.`ifName` AS `remote_ifname`,
                             `P2`.`ifSpeed` AS `remote_ifspeed`,
                             `P2`.`ifOperStatus` AS `remote_ifoperstatus`,
                             `P2`.`ifAdminStatus` AS `remote_ifadminstatus`,
                             `P2`.`ifInOctets_rate` AS `remote_ifinoctets_rate`,
                             `P2`.`ifOutOctets_rate` AS `remote_ifoutoctets_rate`
                      FROM `links`
                             LEFT JOIN `devices` AS `D1` ON `D1`.`device_id`=`links`.`local_device_id`
                             LEFT JOIN `devices` AS `D2` ON `D2`.`device_id`=`links`.`remote_device_id`
                             LEFT JOIN `ports` AS `P1` ON `P1`.`port_id`=`links`.`local_port_id`
                             LEFT JOIN `ports` AS `P2` ON `P2`.`port_id`=`links`.`remote_port_id`
                             $join_sql
                      WHERE
                             `active`=1 AND
                             `local_device_id` != 0 AND
                             `remote_device_id` != 0 AND
                             `local_device_id` IS NOT NULL AND
                             `remote_device_id` IS NOT NULL
                             $sql
                      GROUP BY `P1`.`port_id`,`P2`.`port_id`
                      ", $sql_array);
}

$list = array_merge($ports,$devices);

foreach ($list as $items) {
    $local_device = array('device_id'=>$items['local_device_id'], 'os'=>$items['local_os'], 'hostname'=>$items['local_hostname']);
    $remote_device = array('device_id'=>$items['remote_device_id'], 'os'=>$items['remote_os'], 'hostname'=>$items['remote_hostname']);

    $local_port = array('port_id'=>$items['local_port_id'],'device_id'=>$items['local_port_device_id'],'ifName'=>$items['local_ifname'],'ifSpeed'=>$items['local_ifspeed'],'ifOperStatus'=>$items['local_ifoperstatus'],'ifAdminStatus'=>$items['local_adminstatus']);
    $remote_port = array('port_id'=>$items['remote_port_id'],'device_id'=>$items['remote_port_device_id'],'ifName'=>$items['remote_ifname'],'ifSpeed'=>$items['remote_ifspeed'],'ifOperStatus'=>$items['remote_ifoperstatus'],'ifAdminStatus'=>$items['remote_adminstatus']);

    if (!in_array($items['local_device_id'],$tmp_ids)) {
        $tmp_devices[] = array('id'=>$items['local_device_id'],'label'=>$items['local_hostname'],'title'=>generate_device_link($local_device,'',array(),'','','',0),'shape'=>'box');
    }
    array_push($tmp_ids,$items['local_device_id']);
    if (!in_array($items['remote_device_id'],$tmp_ids)) {
        $tmp_devices[] = array('id'=>$items['remote_device_id'],'label'=>$items['remote_hostname'],'title'=>generate_device_link($remote_device,'',array(),'','','',0),'shape'=>'box');
    }
    array_push($tmp_ids,$items['remote_device_id']);
    $speed = $items['local_ifspeed']/1000/1000;
    if ($speed == 100) {
        $width = 3;
    } 
    elseif ($speed == 1000) {
        $width = 5;
    } 
    elseif ($speed == 10000) {
        $width = 10;
    } 
    elseif ($speed == 40000) {
        $width = 15;
    } 
    elseif ($speed == 100000) {
        $width = 20;
    } 
    else {
        $width = 1;
    }
    $link_in_used = ($items['local_ifinoctets_rate'] * 8) / $items['local_ifspeed'] * 100;
    $link_out_used = ($items['local_ifoutoctets_rate'] * 8) / $items['local_ifspeed'] * 100;
    if ($link_in_used > $link_out_used) {
        $link_used = $link_in_used;
    } 
    else {
        $link_used = $link_out_used;
    }
    $link_used = round($link_used, -1);
    if ($link_used > 100) {
        $link_used = 100;
    }
    $link_color = $config['network_map_legend'][$link_used];
    $id1 = $items['local_port_id'].':'.$items['remote_port_id'];
    $id2 = $items['remote_port_id'].':'.$items['local_port_id'];
    if (!in_array($id1,$tmp_link_ids) || !in_array($id2,$tmp_link_ids)) {
        $local_port = ifNameDescr($local_port);
        $remote_port = ifNameDescr($remote_port);
        $tmp_links[] = array('from'=>$items['local_device_id'],'to'=>$items['remote_device_id'],'label'=>shorten_interface_type($local_port['ifName']) . ' > ' . shorten_interface_type($remote_port['ifName']),'title'=>generate_port_link($local_port, "<img src='graph.php?type=port_bits&amp;id=".$items['local_port_id']."&amp;from=".$config['time']['day']."&amp;to=".$config['time']['now']."&amp;width=100&amp;height=20&amp;legend=no&amp;bg=".str_replace("#","", $row_colour)."'>\n",'',0,1),'width'=>$width,'color'=>$link_color);
    }
    array_push($tmp_link_ids,$id1);
    array_push($tmp_link_ids,$id2);
}

$node_devices = $tmp_devices;
$nodes = json_encode($node_devices);
$edges = json_encode($tmp_links);

if (count($node_devices) > 1 && count($tmp_links) > 0) {
 
?>
 
<div id="visualization"></div>
<script src="js/vis.min.js"></script>
<script type="text/javascript">
var height = $(window).height() - 100;
$('#visualization').height(height + 'px');
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
    var options =  <?php echo $config['network_map_vis_options']; ?>;
    var network = new vis.Network(container, data, options);
    network.on('click', function (properties) {
        if (properties.nodes > 0) {
            window.location.href = "device/device="+properties.nodes+"/tab=map/"
        }
    });
</script>

<?php

} 
else {

    print_message("No map to display, this may be because you aren't running autodiscovery or no devices are linked by mac address.");

}

$pagetitle[] = "Map";
