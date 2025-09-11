<?php

require_once 'includes/systemd-shared.inc.php';

$rrdArray = [];
$state_type = 'load';

foreach ($systemd_mapper[$state_type] as $state_status => $rrd_location) {
    $rrdArray[$state_type][$state_status] = [
        'descr' => $state_status,
        'rrd_location' => $rrd_location,
    ];
}

require 'systemd-common.inc.php';
