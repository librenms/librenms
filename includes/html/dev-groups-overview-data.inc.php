<?php

$device_groups = dbFetchRows('SELECT dg.id, dg.name FROM device_group_device AS d, device_groups AS dg WHERE dg.id=d.device_group_id AND d.device_id=? ORDER BY dg.name', [$device['device_id']]);

if (count($device_groups)) {
    ?>
    <div class='row'>
        <div class='col-md-12'>
            <div class='panel panel-default panel-condensed device-overview'>
                <div class='panel-heading'>
                    <a href="<?=url('device-groups')?>">
                        <i class="fa fa-th fa-lg icon-theme" aria-hidden="true"></i>
                        <strong>Device Group Membership</strong>
                    </a>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-sm-12">
                        <?php foreach ($device_groups as $group) { ?>
                            <span style="margin: 8px;">
                                <a href="<?=url('devices/group=' . $group['id'])?>" target="_blank"><?=$group['name']?></a>
                            </span>
                        <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
