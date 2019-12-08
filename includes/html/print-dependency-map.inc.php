<?php

use LibreNMS\Config;

if (!Auth::user()->hasGlobalRead()) {
    $sql = 'WHERE device_id IN (SELECT device_id FROM devices_perms WHERE user_id='. Auth::id() . ')';
} else {
    $sql = '';
}

$devices_by_id = array();
$dependencies = array();

$devices = dbFetchRows('SELECT * FROM devices ' . $sql);
$device_dependencies = dbFetchRows('SELECT * FROM device_relationships');

// Build the style variables we need
$node_disabled_style = array(
    'color' => array(
        'highlight' => array(
            'background' => Config::get('network_map_legend.di.node'),
        ),
        'border' => Config::get('network_map_legend.di.border'),
        'background' => Config::get('network_map_legend.di.node'),
    ),
);
$node_down_style = array(
    'color' => array(
        'highlight' => array(
            'background' => Config::get('network_map_legend.dn.node'),
            'border' => Config::get('network_map_legend.dn.border'),
        ),
        'border' => Config::get('network_map_legend.dn.border'),
        'background' => Config::get('network_map_legend.dn.node'),
    ),
);

// List all devices
foreach ($devices as $items) {
    $device_attributes = array(
        'device_id' => $items['device_id'],
        'os' => $items['os'],
        'hostname' => $items['hostname'],
    );

    $device_id = $items['device_id'];

    if ($items['disabled']) {
        $device_style = $node_disabled_style;
    } elseif (! $items['status']) {
        $device_style = $node_down_style;
    } else {
        $device_style = array();
    }

    $devices_by_id[$device_id] = array_merge(
        array(
            'id' => $device_id,
            'label' => shorthost(format_hostname($items, $items['hostname']), 1),
            'title' => generate_device_link($device_attributes, '', array(), '', '', '', 0),
            'shape' => 'box',
        ),
        $device_style
    );
}

// List all Device Dependencies
foreach ($device_dependencies as $items) {
    $dependencies[] = array_merge(
        array(
            'from' => $items['child_device_id'],
            'to' => $items['parent_device_id'],
            'width' => 2,
        )
    );
}

$nodes = json_encode(array_values($devices_by_id));
$edges = json_encode($dependencies);

if (count($devices_by_id)) {
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
    var options =  <?php echo Config::get('network_map_vis_options'); ?>;
var network = new vis.Network(container, data, options);
    network.on('click', function (properties) {
        if (properties.nodes > 0) {
            window.location.href = "device/device="+properties.nodes+"/tab=neighbours/selection=map/"
        }
    });
</script>

<?php
} else {
    print_message("No devices found");
}

$pagetitle[] = "Device Dependency Map";
