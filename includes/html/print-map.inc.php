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

use LibreNMS\Config;

$highlight_node = $vars['highlight_node'] ?? 0;
$group = $vars['group'] ?? 0;

//Don't know where this should come from, but it is used later, so I just define it here.
$row_colour = '#ffffff';

$sql_array = [];
if (! empty($device['hostname'])) {
    $sql = ' AND (`D1`.`hostname`=? OR `D2`.`hostname`=?)';
    $sql_array = [$device['hostname'], $device['hostname']];
    $mac_sql = ' AND `D`.`hostname` = ?';
    $mac_array = [$device['hostname']];
} else {
    $sql = ' ';
}

if (! Auth::user()->hasGlobalRead()) {
    $device_ids = Permissions::devicesForUser()->toArray() ?: [0];
    $sql .= ' AND `D1`.`device_id` IN ' . dbGenPlaceholders(count($device_ids));
    $sql .= ' AND `D2`.`device_id` IN ' . dbGenPlaceholders(count($device_ids));
    $sql_array = array_merge($sql_array, $device_ids, $device_ids);
}

$devices_by_id = [];
$links = [];
$link_assoc_seen = [];
$device_assoc_seen = [];
$ports = [];
$devices = [];

$group_name = '';
$where = '';
if (is_numeric($group) && $group) {
    $group_name = dbFetchCell('SELECT `name` from `device_groups` WHERE `id` = ?', [$group]);
    $where .= ' AND D1.device_id IN (SELECT `device_id` FROM `device_group_device` WHERE `device_group_id` = ?)';
    $sql_array[] = $group;
    $where .= ' OR D2.device_id IN (SELECT `device_id` FROM `device_group_device` WHERE `device_group_id` = ?)';
    $sql_array[] = $group;
}

if (in_array('mac', Config::get('network_map_items'))) {
    $ports = dbFetchRows("SELECT
                             `D1`.`status` AS `local_status`,
                             `D1`.`device_id` AS `local_device_id`,
                             `D1`.`disabled` AS `local_disabled`,
                             `D1`.`os` AS `local_os`,
                             `D1`.`hostname` AS `local_hostname`,
                             `D1`.`sysName` AS `local_sysName`,
                             `D2`.`status` AS `remote_status`,
                             `D2`.`device_id` AS `remote_device_id`,
                             `D2`.`disabled` AS `remote_disabled`,
                             `D2`.`os` AS `remote_os`,
                             `D2`.`hostname` AS `remote_hostname`,
                             `D2`.`sysName` AS `remote_sysName`,
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
                             `P2`.`ifOutOctets_rate` AS `remote_ifoutoctets_rate`,
                             SUM(IF(`P2_ip`.`ipv4_address` = `M`.`ipv4_address`, 1, 0))
                                 AS `remote_matching_ips`
                      FROM `ipv4_mac` AS `M`
                             INNER JOIN `ports` AS `P1` ON `P1`.`port_id`=`M`.`port_id`
                             INNER JOIN `ports` AS `P2` ON `P2`.`ifPhysAddress`=`M`.`mac_address`
                             INNER JOIN `devices` AS `D1` ON `P1`.`device_id`=`D1`.`device_id`
                             INNER JOIN `devices` AS `D2` ON `P2`.`device_id`=`D2`.`device_id`
                             INNER JOIN `ipv4_addresses` AS `P2_ip` ON `P2_ip`.`port_id` = `P2`.`port_id`
                             $join_sql
                      WHERE
                             `M`.`mac_address` NOT IN ('000000000000','ffffffffffff') AND
                             `D1`.`device_id` != `D2`.`device_id`
                             $where
                             $sql
                      GROUP BY `P1`.`port_id`,`P2`.`port_id`,`D1`.`device_id`, `D1`.`os`, `D1`.`hostname`, `D2`.`device_id`, `D2`.`os`, `D2`.`hostname`, `P1`.`port_id`, `P1`.`device_id`, `P1`.`ifName`, `P1`.`ifSpeed`, `P1`.`ifOperStatus`, `P1`.`ifAdminStatus`, `P1`.`ifInOctets_rate`, `P1`.`ifOutOctets_rate`, `P2`.`port_id`, `P2`.`device_id`, `P2`.`ifName`, `P2`.`ifSpeed`, `P2`.`ifOperStatus`, `P2`.`ifAdminStatus`, `P2`.`ifInOctets_rate`, `P2`.`ifOutOctets_rate`
                      ORDER BY `remote_matching_ips` DESC, `local_ifname`, `remote_ifname`
                     ", $sql_array);
}

if (in_array('xdp', Config::get('network_map_items'))) {
    $devices = dbFetchRows("SELECT
                             `D1`.`status` AS `local_status`,
                             `D1`.`device_id` AS `local_device_id`,
                             `D1`.`os` AS `local_os`,
                             `D1`.`disabled` AS `local_disabled`,
                             `D1`.`hostname` AS `local_hostname`,
                             `D1`.`sysName` AS `local_sysName`,
                             `D2`.`status` AS `remote_status`,
                             `D2`.`device_id` AS `remote_device_id`,
                             `D2`.`disabled` AS `remote_disabled`,
                             `D2`.`os` AS `remote_os`,
                             `D2`.`hostname` AS `remote_hostname`,
                             `D2`.`sysName` AS `remote_sysName`,
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
                             INNER JOIN `devices` AS `D1` ON `D1`.`device_id`=`links`.`local_device_id`
                             INNER JOIN `devices` AS `D2` ON `D2`.`device_id`=`links`.`remote_device_id`
                             INNER JOIN `ports` AS `P1` ON `P1`.`port_id`=`links`.`local_port_id`
                             INNER JOIN `ports` AS `P2` ON `P2`.`port_id`=`links`.`remote_port_id`
                             $join_sql
                      WHERE
                             `active`=1 AND
                             `local_device_id` != 0 AND
                             `remote_device_id` != 0
                             $where
                             $sql
                      GROUP BY `P1`.`port_id`,`P2`.`port_id`,`D1`.`device_id`, `D1`.`os`, `D1`.`hostname`, `D2`.`device_id`, `D2`.`os`, `D2`.`hostname`, `P1`.`port_id`, `P1`.`device_id`, `P1`.`ifName`, `P1`.`ifSpeed`, `P1`.`ifOperStatus`, `P1`.`ifAdminStatus`, `P1`.`ifInOctets_rate`, `P1`.`ifOutOctets_rate`, `P2`.`port_id`, `P2`.`device_id`, `P2`.`ifName`, `P2`.`ifSpeed`, `P2`.`ifOperStatus`, `P2`.`ifAdminStatus`, `P2`.`ifInOctets_rate`, `P2`.`ifOutOctets_rate`
                      ORDER BY `local_ifname`, `remote_ifname`
                      ", $sql_array);
}

$list = array_merge($ports, $devices);

// Build the style variables we need

$node_disabled_style = [
    'color' => [
        'highlight' => [
            'background' => Config::get('network_map_legend.di.node'),
        ],
        'border' => Config::get('network_map_legend.di.border'),
        'background' => Config::get('network_map_legend.di.node'),
    ],
];
$node_down_style = [
    'color' => [
        'highlight' => [
            'background' => Config::get('network_map_legend.dn.node'),
            'border' => Config::get('network_map_legend.dn.border'),
        ],
        'border' => Config::get('network_map_legend.dn.border'),
        'background' => Config::get('network_map_legend.dn.node'),
    ],
];
$node_highlight_style = [
    'color' => [
        'highlight' => [
            'border' => Config::get('network_map_legend.highlight.border'),
        ],
        'border' => Config::get('network_map_legend.highlight.border'),
    ],
    'borderWidth' => Config::get('network_map_legend.highlight.borderWidth'),
];
$edge_disabled_style = [
    'dashes' => [8, 12],
    'color' => [
        'color' => Config::get('network_map_legend.di.edge'),
        'highlight' => Config::get('network_map_legend.di.edge'),
    ],
];
$edge_down_style = [
    'dashes' => [8, 12],
    'color' => [
        'border' => Config::get('network_map_legend.dn.border'),
        'highlight' => Config::get('network_map_legend.dn.edge'),
        'color' => Config::get('network_map_legend.dn.edge'),
    ],
];

// Iterate though ports and links, generating a set of devices (nodes)
// and links (edges) that make up the topology graph.

foreach ($list as $items) {
    $local_device = [
        'device_id'=>$items['local_device_id'],
        'os'=>$items['local_os'],
        'hostname'=>$items['local_hostname'],
    ];
    $remote_device = [
        'device_id'=>$items['remote_device_id'],
        'os'=>$items['remote_os'],
        'hostname'=>$items['remote_hostname'],
    ];
    $local_port = [
        'port_id'=>$items['local_port_id'],
        'device_id'=>$items['local_port_device_id'],
        'ifName'=>$items['local_ifname'],
        'ifSpeed'=>$items['local_ifspeed'],
        'ifOperStatus'=>$items['local_ifoperstatus'],
        'ifAdminStatus'=>$items['local_adminstatus'],
    ];
    $remote_port = [
        'port_id'=>$items['remote_port_id'],
        'device_id'=>$items['remote_port_device_id'],
        'ifName'=>$items['remote_ifname'],
        'ifSpeed'=>$items['remote_ifspeed'],
        'ifOperStatus'=>$items['remote_ifoperstatus'],
        'ifAdminStatus'=>$items['remote_adminstatus'],
    ];

    $local_device_id = $items['local_device_id'];
    if (! array_key_exists($local_device_id, $devices_by_id)) {
        $items['sysName'] = $items['local_sysName'];
        $devices_by_id[$local_device_id] = [
            'id'=>$local_device_id,
            'label'=>shorthost(format_hostname($items, $items['local_hostname']), 1),
            'title'=>generate_device_link($local_device, '', [], '', '', '', 0),
            'shape'=>'box',
        ];
        if ($items['local_disabled'] != '0') {
            $devices_by_id[$local_device_id] = array_merge($devices_by_id[$local_device_id], $node_disabled_style);
        } elseif ($items['local_status'] == '0') {
            $devices_by_id[$local_device_id] = array_merge($devices_by_id[$local_device_id], $node_down_style);
        }

        if ((empty($device['hostname'])) && ($local_device_id == $highlight_node)) {
            $devices_by_id[$local_device_id] = array_merge($devices_by_id[$local_device_id], $node_highlight_style);
        }
    }

    $remote_device_id = $items['remote_device_id'];
    if (! array_key_exists($remote_device_id, $devices_by_id)) {
        $items['sysName'] = $items['remote_sysName'];
        $devices_by_id[$remote_device_id] = ['id'=>$remote_device_id, 'label'=>shorthost(format_hostname($items, $items['remote_hostname']), 1), 'title'=>generate_device_link($remote_device, '', [], '', '', '', 0), 'shape'=>'box'];
        if ($items['remote_disabled'] != '0') {
            $devices_by_id[$remote_device_id] = array_merge($devices_by_id[$remote_device_id], $node_disabled_style);
        } elseif ($items['remote_status'] == '0') {
            $devices_by_id[$remote_device_id] = array_merge($devices_by_id[$remote_device_id], $node_down_style);
        }

        if ((empty($device['hostname'])) && ($remote_device_id == $highlight_node)) {
            $devices_by_id[$remote_device_id] = array_merge($devices_by_id[$remote_device_id], $node_highlight_style);
        }
    }

    $speed = $items['local_ifspeed'] / 1000 / 1000;
    if ($speed > 500000) {
        $width = 20;
    } else {
        $width = round(0.77 * pow($speed, 0.25));
    }
    $link_in_used = $items['local_ifspeed'] ? (($items['local_ifinoctets_rate'] * 8) / $items['local_ifspeed'] * 100) : 0;
    $link_out_used = $items['local_ifspeed'] ? (($items['local_ifoutoctets_rate'] * 8) / $items['local_ifspeed'] * 100) : 0;
    if ($link_in_used > $link_out_used) {
        $link_used = $link_in_used;
    } else {
        $link_used = $link_out_used;
    }
    $link_used = round(2 * $link_used, -1) / 2;
    if ($link_used > 100) {
        $link_used = 100;
    }
    if (is_nan($link_used)) {
        $link_used = 0;
    }
    $link_style = [
        'color' => [
            'border' => Config::get("network_map_legend.$link_used"),
            'highlight' => Config::get("network_map_legend.$link_used"),
            'color' => Config::get("network_map_legend.$link_used"),
        ],
    ];

    if (($items['remote_ifoperstatus'] == 'down') || ($items['local_ifoperstatus'] == 'down')) {
        $link_style = $edge_down_style;
    }
    if (($items['remote_disabled'] != '0') && ($items['local_disabled'] != '0')) {
        $link_style = $edge_disabled_style;
    } elseif (($items['remote_status'] == '0') && ($items['local_status'] == '0')) {
        $link_style = $edge_down_style;
    } elseif (($items['remote_status'] == '1' && $items['remote_ifoperstatus'] == 'down') || ($items['local_status'] == '1' && $items['local_ifoperstatus'] == 'down')) {
        $link_style = $edge_down_style;
    }

    $link_id1 = $items['local_port_id'] . ':' . $items['remote_port_id'];
    $link_id2 = $items['remote_port_id'] . ':' . $items['local_port_id'];
    $device_id1 = $items['local_device_id'] . ':' . $items['remote_device_id'];
    $device_id2 = $items['remote_device_id'] . ':' . $items['local_device_id'];

    // If mac is choosen to graph, ensure only one link exists between any two ports, or any two devices.
    // else ensure only one link exists between any two ports
    if (! array_key_exists($link_id1, $link_assoc_seen) &&
        ! array_key_exists($link_id2, $link_assoc_seen) &&
        (! in_array('mac', Config::get('network_map_items')) ||
        (! array_key_exists($device_id1, $device_assoc_seen) &&
        ! array_key_exists($device_id2, $device_assoc_seen)))) {
        $local_port = cleanPort($local_port);
        $remote_port = cleanPort($remote_port);
        $links[] = array_merge(
            [
                'from'=>$items['local_device_id'],
                'to'=>$items['remote_device_id'],
                'label'=> \LibreNMS\Util\Rewrite::shortenIfType($local_port['ifName']) . ' > ' . \LibreNMS\Util\Rewrite::shortenIfType($remote_port['ifName']),
                'title' => generate_port_link($local_port, "<img src='graph.php?type=port_bits&amp;id=" . $items['local_port_id'] . '&amp;from=' . Config::get('time.day') . '&amp;to=' . Config::get('time.now') . '&amp;width=100&amp;height=20&amp;legend=no&amp;bg=' . str_replace('#', '', $row_colour) . "'>\n", '', 0, 1),
                'width'=>$width,
            ],
            $link_style
        );
    }
    $link_assoc_seen[$link_id1] = 1;
    $link_assoc_seen[$link_id2] = 1;
    $device_assoc_seen[$device_id1] = 1;
    $device_assoc_seen[$device_id2] = 1;
}

$nodes = json_encode(array_values($devices_by_id));
$edges = json_encode($links);

array_multisort(array_column($devices_by_id, 'label'), SORT_ASC, $devices_by_id);

if (count($devices_by_id) > 1 && count($links) > 0) {
    ?>

<form name="printmapform" method="get" action="" class="form-horizontal" role="form">
    <?php if (empty($device['hostname'])) { ?>
<big><b><?=$group_name?></b></big>
<div class="pull-right">
<select name="highlight_node" id="highlight_node" class="input-sm" onChange="highlightNode()";>
<option value="0">None</option>
        <?php foreach ($devices_by_id as $dev) { ?>
<option value="<?=$dev['id']?>"><?=$dev['label']?></option>
        <?php } ?>
</select>
</div>
    <?php } ?>
<div id="visualization"></div>
</form>
<script src="js/vis.min.js"></script>
<script type="text/javascript">
var height = $(window).height() - 100;
$('#visualization').height(height + 'px');
    // create an array with nodes
    var nodes =
    <?php
    echo $nodes; ?>
    ;

    // create an array with edges
    var edges =
    <?php
    echo $edges; ?>
    ;

    // create a network
    var container = document.getElementById('visualization');
    var data = {
        nodes: nodes,
        edges: edges,
        stabilize: true
    };
    var options =  <?php echo Config::get('network_map_vis_options'); ?>;
var network = new vis.Network(container, data, options);
    network.on('click', function (properties) {
        if (properties.nodes > 0) {
            window.location.href = "device/device="+properties.nodes+"/tab=neighbours/selection=map/"
        }
    });

function highlightNode(e) {
    highlight_node = document.getElementById("highlight_node").value;
    <?php
    if ($group) {
        echo "window.location.pathname = 'map/group=" . $group . "/highlight_node=' + highlight_node;";
    } else {
        echo "window.location.pathname = 'map/highlight_node=' + highlight_node;";
    } ?>
}

$('#highlight_node option[value="<?=$highlight_node?>"]').prop('selected', true);
</script>

    <?php
} else {
        print_message("No map to display, this may be because you aren't running autodiscovery or no devices are linked by mac address.");
    }

$pagetitle[] = 'Map';
