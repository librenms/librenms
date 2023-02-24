<?php

$COMPONENT = new LibreNMS\Component();
$options = [];
$options['filter']['ignore'] = ['=', 0];
$options['type'] = 'Cisco-OTV';
$COMPONENTS = $COMPONENT->getComponents(null, $options);

foreach ($COMPONENTS as $DEVICE_ID => $COMP) {
    $LINK = \LibreNMS\Util\Url::generate(['page' => 'device', 'device' => $DEVICE_ID, 'tab' => 'routing', 'proto' => 'cisco-otv']); ?>
<div class="panel panel-default" id="overlays-<?php echo $DEVICE_ID?>">
    <div class="panel-heading">
        <h3 class="panel-title"><a href="<?php echo $LINK?>"><?php echo gethostbyid($DEVICE_ID)?> - Overlay's & Adjacencies</a></h3>
    </div>
    <div class="panel list-group">
        <?php
        // Loop over each component, pulling out the Overlays.
        foreach ($COMP as $OID => $OVERLAY) {
            if ($OVERLAY['otvtype'] == 'overlay') {
                if ($OVERLAY['status'] == 0) {
                    $OVERLAY_STATUS = "<span class='green pull-right'>Normal</span>";
                    $GLI = '';
                } else {
                    $OVERLAY_STATUS = "<span class='pull-right'>" . $OVERLAY['error'] . " - <span class='red'>Alert</span></span>";
                    $GLI = 'list-group-item-danger';
                } ?>
                <a class="list-group-item <?php echo $GLI?>" data-toggle="collapse" data-target="#<?php echo $OVERLAY['index']?>" data-parent="#overlays-<?php echo $DEVICE_ID?>"><?php echo $OVERLAY['label']?> - <?php echo $OVERLAY['transport']?> <?php echo $OVERLAY_STATUS?></a>
                <div id="<?php echo $OVERLAY['index']?>" class="sublinks collapse">
                    <?php
                    foreach ($COMP as $AID => $ADJACENCY) {
                        if (($ADJACENCY['otvtype'] == 'adjacency') && ($ADJACENCY['index'] == $OVERLAY['index'])) {
                            if ($ADJACENCY['status'] == 0) {
                                $ADJ_STATUS = "<span class='green pull-right'>Normal</span>";
                                $GLI = '';
                            } else {
                                $ADJ_STATUS = "<span class='pull-right'>" . $ADJACENCY['error'] . " - <span class='red'>Alert</span></span>";
                                $GLI = 'list-group-item-danger';
                            } ?>
                            <a class="list-group-item <?php echo $GLI?> small"><i class="fa fa-chevron-right" aria-hidden="true"></i> <?php echo $ADJACENCY['label']?> - <?php echo $ADJACENCY['endpoint']?> <?php echo $ADJ_STATUS?></a>
                            <?php
                        }
                    } ?>
                </div>
                <?php
            }
        } ?>
    </div>
</div>
    <?php
}
