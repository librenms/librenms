<?php

require_once "../includes/component.php";
$COMPONENT = new component();
$options = array();
$options['filter']['ignore'] = array('=',0);
$options['type'] = 'Cisco-OTV';
$COMPONENTS = $COMPONENT->getComponents(null,$options);

foreach ($COMPONENTS as $DEVICE_ID => $COMP) {
    $LINK = generate_url(array('page' => 'device', 'device' => $DEVICE_ID, 'tab' => 'routing', 'proto' => 'cisco-otv'));
?>
<div class="panel panel-default" id="overlays-<?=$DEVICE_ID?>">
    <div class="panel-heading">
        <h3 class="panel-title"><a href="<?=$LINK?>"><?=gethostbyid($DEVICE_ID)?> - Overlay's & Adjacencies</a></h3>
    </div>
    <div class="panel list-group">
        <?php
        // Loop over each component, pulling out the Overlays.
        foreach ($COMP as $OID => $OVERLAY) {
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
                <a class="list-group-item <?=$GLI?>" data-toggle="collapse" data-target="#<?=$OVERLAY['index']?>" data-parent="#overlays-<?=$DEVICE_ID?>"><?=$OVERLAY['label']?> - <?=$OVERLAY['transport']?> <?=$OVERLAY_STATUS?></a>
                <div id="<?=$OVERLAY['index']?>" class="sublinks collapse">
                    <?php
                    foreach ($COMP as $AID => $ADJACENCY) {
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
<?php
}
