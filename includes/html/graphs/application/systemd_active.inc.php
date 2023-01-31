<?php

require_once 'includes/systemd-shared.inc.php';

$rrdArray = [];
foreach ($systemd_mapper['active'] as $state_status) {
    $rrdArray['active'][$state_status] = ['descr' => $state_status];
}

require 'systemd-common.inc.php';
