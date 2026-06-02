<?php

$device_groups = dbFetchRows('SELECT dg.id, dg.name FROM device_group_device AS d, device_groups AS dg WHERE dg.id=d.device_group_id AND d.device_id=? ORDER BY dg.name', [$device['device_id']]);

if (count($device_groups)) {
    ?>
    <div class='overview-panel tw:mb-5'>
                <div class='overview-panel-heading'>
                    <a href="<?=url('device-groups')?>">
                        <i class="fa fa-th fa-lg icon-theme" aria-hidden="true"></i>
                        <strong>Device Group Membership</strong>
                    </a>
                </div>
                <div class="overview-panel-body">
                    <div class="tw:flex tw:flex-wrap tw:gap-2 tw:p-2">
                        <?php foreach ($device_groups as $group) { ?>
                            <span>
                                <a href="<?=route('devices', ['filter' => ['groups.id' => ['eq' => $group['id']]]])?>" target="_blank"><?=htmlspecialchars((string) $group['name'])?></a>
                            </span>
                        <?php } ?>
                    </div>
                </div>
            </div>
    <?php
}
