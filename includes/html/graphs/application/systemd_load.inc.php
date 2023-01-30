<?php

require_once 'includes/systemd-shared.inc.php';

$rrdArray = [];
foreach ($systemd_mapper['load'] as $state_status) {
    $rrdArray['load'][$state_status] = ['descr' => $state_status];
}

require 'systemd-common.inc.php';
