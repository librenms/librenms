<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2019 Vitali Kari
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

use LibreNMS\Config;

$hops = [];
$links = [];

$options = Config::get('network_map_vis_options');

$lsp_path_id = $path['lsp_path_id'];
$last_node = dbFetchCell('SELECT L.mplsLspToAddr FROM mpls_lsps AS L, mpls_lsp_paths AS P WHERE P.lsp_path_id = ? AND L.lsp_id = P.lsp_id', [$path['lsp_path_id']]);
$device_id = $device['device_id'];

$ar_list = dbFetchRows('SELECT * from `mpls_tunnel_ar_hops` where device_id = ? AND lsp_path_id = ?', [$device_id, $lsp_path_id]);
d_echo($ar_list);

// first node is host self
$node = device_has_ip($ar_list[0]['mplsTunnelARHopRouterId']);
if ($node) {
    $node_id = $node['device_id'];
    $label = $node['hostname'];
    $first_node = $ar_list[0]['mplsTunnelARHopRouterId'];
} else {
    $node_id = $label = $first_node;
}

foreach ($ar_list as $value) {
    $node = device_has_ip($value['mplsTunnelARHopRouterId']);
    if ($node) {
        $remote_node_id = $node['device_id'];
        $remote_label = $node['hostname'];
    } else {
        $remote_node_id = $remote_label = $value['mplsTunnelARHopRouterId'];
    }

    $hops[$remote_node_id] = [
        'id' => $remote_node_id,
        'label' => $remote_label . PHP_EOL . $value['mplsTunnelARHopRouterId'],
    ];

    if ($value['nextNodeProtected'] == 'true') {
        $hops[$remote_node_id]['color'] = '#ccffcc';
        $hops[$remote_node_id]['title'] = 'Node Protected';
    } else {
        $hops[$remote_node_id]['color'] = '#cccccc';
        $hops[$remote_node_id]['title'] = 'Node Not Protected';
    }

    if ($value['mplsTunnelARHopRouterId'] == $first_node || $value['mplsTunnelARHopRouterId'] == $last_node) {
        $hops[$remote_node_id]['shape'] = 'circle';
    }

    $lsp = dbFetchCell('SELECT L.mplsLspName FROM mpls_lsps AS L, mpls_lsp_paths AS P WHERE P.lsp_path_id = ? AND L.lsp_id = P.lsp_id', [$value['lsp_path_id']]);

    if ($value['localProtected'] == 'true') {
        $link_color = '#004d00';
    } else {
        $link_color = '#000000';
    }

    $links[] = [
        'from' => $node_id,
        'to' => $remote_node_id,
        'label' => strval($value['mplsTunnelARHopIpv4Addr']),
        'font' => [
            'align' => 'top',
            'color' => $link_color,
        ],
        'title' => $lsp . ' active hop #' . strval($value['mplsTunnelARHopIndex']) . ' Link Protected: ' . $value['localProtected'],
        'width' => 4.0,
        'color' => [
            'color' => $link_color,
            'opacity' => '0.6',
        ],
        'selfReferenceSize' => 45,
    ];

    // process next hop
    $node_id = $remote_node_id;
    $label = $remote_label;
}

// try to find the computed CSPF Path
$dev_mpls_tunnel_c_hops = collect(dbFetchRows('SELECT * FROM mpls_tunnel_c_hops where device_id = ?', [$device_id])); // collect all computed hops
$keyed = $dev_mpls_tunnel_c_hops->keyBy('mplsTunnelCHopListIndex'); // reduce to last hops

// Filter to only with final destination
$filtered = $keyed->filter(function ($value) use ($last_node) {
    return $value['mplsTunnelCHopRouterId'] == $last_node;
});
// FIXME pick the last one, but it seems that the secod one could work too. On NOKIA it actually does not matter, the paths have the same hops.
// The first one is the active route path.
$filtered2 = $filtered->last()['mplsTunnelCHopListIndex'];

// get CSPF List
$c_list = dbFetchRows('SELECT * from `mpls_tunnel_c_hops` where device_id = ? AND mplsTunnelCHopListIndex = ?', [$device_id, $filtered2]);

// first node is host self
$node = device_has_ip($c_list[0]['mplsTunnelCHopRouterId']);
if ($node) {
    $node_id = $node['device_id'];
    $label = $node['hostname'];
} else {
    $node_id = $label = $c_list[0]['mplsTunnelCHopRouterId'];
}

foreach ($c_list as $value) {
    $node = device_has_ip($value['mplsTunnelCHopRouterId']);
    if ($node) {
        $remote_node_id = $node['device_id'];
        $remote_label = $node['hostname'];
    } else {
        $remote_node_id = $remote_label = $value['mplsTunnelCHopRouterId'];
    }

    if (empty($hops[$remote_node_id])) {
        $hops[$remote_node_id] = [
            'id' => $remote_node_id,
            'label' => $remote_label . PHP_EOL . $value['mplsTunnelCHopRouterId'],
            'color' => '#cccccc',
            'title' => 'Node Protection Unknown',
        ];
    }

    $links[] = [
        'from' => $node_id,
        'to' => $remote_node_id,
        'label' => strval($value['mplsTunnelCHopIpv4Addr']),
        'font' => [
            'align' => 'bottom',
            'color' => '#262626',
        ],
        'title' => 'computed detour hop # ' . strval($value['mplsTunnelCHopIndex']),
        'width' => 4.0,
        'color' => [
            'color' => '#262626',
            'opacity' => '0.5',
        ],
        'selfReferenceSize' => 25,
        'dashes' => 'true',
    ];

    // process next hop
    $node_id = $remote_node_id;
    $label = $remote_label;
}

$nodes = json_encode(array_values($hops));
$edges = json_encode($links);
if (count($hops) > 1 && count($links) > 0) {
    $visualization = 'visualization-' . $i;
    echo '<div id="visualization-' . $i . '"></div>
        <script src="js/vis.min.js"></script>
        <script type="text/javascript">
        var height = $(window).height() / 2;
        ';
    echo "$('#" . $visualization . "').height(height + 'px');
        var nodes = " . $nodes . ';
        var edges = ' . $edges . ';
        ';
    echo "var container = document.getElementById('" . $visualization . "');
        ";
    echo 'var data = {
            nodes: nodes,
            edges: edges,
            stabilize: true
        };
        var options =  ' . $options . ';
        ';
    echo "var network = new vis.Network(container, data, options);
        network.on('click', function (properties) {
            if (properties.nodes > 0) {
                window.location.href = " . '"device/device="+properties.nodes+"/tab=routing/proto=mpls/view=paths/"
            }
        });
        </script>';
} else {
    print_message('No Path map to display. Maybe there are no MPLS tunnel hops discovered.');
}
