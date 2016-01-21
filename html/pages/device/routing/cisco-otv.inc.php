<?php

require_once "../includes/component.php";
$COMPONENT = new component();
$options = array();
$options['filter']['ignore'] = array('=',0);
$options['type'] = 'Cisco-OTV';
$COMPONENTS = $COMPONENT->getComponents($device['device_id'],$options);
$COMPONENTS = $COMPONENTS[$device['device_id']];

global $config;
?>
<div class="panel panel-default" id="overlays">
    <div class="panel-heading">
        <h3 class="panel-title">Overlay's & Adjacencies</h3>
    </div>
    <div class="panel list-group">
<?php
// Loop over each component, pulling out the Overlays.
foreach ($COMPONENTS as $OID => $OVERLAY) {
    if ($OVERLAY['otvtype'] == 'overlay') {
        if ($OVERLAY['status'] == 1) {
            $OVERLAY_STATUS = "<span class='green pull-right'>Normal</span>";
            $GLI = "";
        }
        else {
            $OVERLAY_STATUS = "<span class='pull-right'>".$OVERLAY['error']." - <span class='red'>Alert</span></span>";
            $GLI = "list-group-item-danger";
        }
?>
        <a class="list-group-item <?=$GLI?>" data-toggle="collapse" data-target="#<?=$OVERLAY['index']?>" data-parent="#overlays"><?=$OVERLAY['label']?> - <?=$OVERLAY['transport']?> <?=$OVERLAY_STATUS?></a>
        <div id="<?=$OVERLAY['index']?>" class="sublinks collapse">
<?php
        foreach ($COMPONENTS as $AID => $ADJACENCY) {
            if (($ADJACENCY['otvtype'] == 'adjacency') && ($ADJACENCY['index'] == $OVERLAY['index'])) {
                if ($ADJACENCY['status'] == 1) {
                    $ADJ_STATUS = "<span class='green pull-right'>Normal</span>";
                    $GLI = "";
                }
                else {
                    $ADJ_STATUS = "<span class='pull-right'>".$ADJACENCY['error']." - <span class='red'>Alert</span></span>";
                    $GLI = "list-group-item-danger";
                }
?>
            <a class="list-group-item <?=$GLI?> small"><span class="glyphicon glyphicon-chevron-right"></span> <?=$ADJACENCY['label']?> - <?=$ADJACENCY['endpoint']?> <?=$ADJ_STATUS?></a>
<?php
            }
        }
?>
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

$graph_array = array();
$graph_array['device'] = $device['device_id'];
$graph_array['height'] = '100';
$graph_array['width']  = '215';
$graph_array['to']     = $config['time']['now'];
$graph_array['type']   = 'device_cisco-otv-vlan';
require 'includes/print-graphrow.inc.php';

?>
    </div>
</div>

<div class="panel panel-default" id="macperendpoint">
    <div class="panel-heading">
        <h3 class="panel-title">MAC Addresses</h3>
    </div>
    <div class="panel-body">
<?php

$graph_array = array();
$graph_array['device'] = $device['device_id'];
$graph_array['height'] = '100';
$graph_array['width']  = '215';
$graph_array['to']     = $config['time']['now'];
$graph_array['type']   = 'device_cisco-otv-mac';
require 'includes/print-graphrow.inc.php';

        ?>
    </div>
</div>
