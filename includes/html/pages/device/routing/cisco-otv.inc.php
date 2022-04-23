<?php

$component = new LibreNMS\Component();
$options = [];
$options['filter']['ignore'] = ['=', 0];
$options['type'] = 'Cisco-OTV';
$components = $component->getComponents($device['device_id'], $options);
$components = $components[$device['device_id']];

?>
<div class="panel panel-default" id="overlays">
    <div class="panel-heading">
        <h3 class="panel-title">Overlay's &amp; Adjacencies</h3>
    </div>
    <div class="panel list-group">
<?php
// Loop over each component, pulling out the Overlays.
foreach ($components as $oid => $overlay) {
    if ($overlay['otvtype'] == 'overlay') {
        if ($overlay['status'] == 0) {
            $overlay_status = "<span class='green pull-right'>Normal</span>";
            $gli = '';
        } else {
            $overlay_status = "<span class='pull-right'>" . $overlay['error'] . " - <span class='red'>Alert</span></span>";
            $gli = 'list-group-item-danger';
        } ?>
        <a class="list-group-item <?php echo $gli?>" data-toggle="collapse" data-target="#<?php echo $overlay['index']?>" data-parent="#overlays"><?php echo $overlay['label']?> - <?php echo $overlay['transport']?> <?php echo $overlay_status?></a>
        <div id="<?php echo $overlay['index']?>" class="sublinks collapse">
        <?php
        foreach ($components as $aid => $adjacency) {
            if (($adjacency['otvtype'] == 'adjacency') && ($adjacency['index'] == $overlay['index'])) {
                if ($adjacency['status'] == 0) {
                    $adj_status = "<span class='green pull-right'>Normal</span>";
                    $gli = '';
                } else {
                    $adj_status = "<span class='pull-right'>" . $adjacency['error'] . " - <span class='red'>Alert</span></span>";
                    $gli = 'list-group-item-danger';
                } ?>
    <a class="list-group-item <?php echo $gli?> small"><i class="fa fa-chevron-right" aria-hidden="true"></i> <?php echo $adjacency['label']?> - <?php echo $adjacency['endpoint']?> <?php echo $adj_status?></a>
                <?php
            }
        } ?>
        </div>
        <?php
    }
}
?>
    </div>
</div>

<div class="panel panel-default" id="vlanperoverlay">
    <div class="panel-heading">
        <h3 class="panel-title">AED Enabled VLAN's</h3>
    </div>
    <div class="panel-body">
<?php

$graph_array = [];
$graph_array['device'] = $device['device_id'];
$graph_array['height'] = '100';
$graph_array['width'] = '215';
$graph_array['to'] = \LibreNMS\Config::get('time.now');
$graph_array['type'] = 'device_cisco-otv-vlan';
require 'includes/html/print-graphrow.inc.php';

?>
    </div>
</div>

<div class="panel panel-default" id="macperendpoint">
    <div class="panel-heading">
        <h3 class="panel-title">MAC Addresses</h3>
    </div>
    <div class="panel-body">
<?php

$graph_array = [];
$graph_array['device'] = $device['device_id'];
$graph_array['height'] = '100';
$graph_array['width'] = '215';
$graph_array['to'] = \LibreNMS\Config::get('time.now');
$graph_array['type'] = 'device_cisco-otv-mac';
require 'includes/html/print-graphrow.inc.php';

?>
    </div>
</div>
