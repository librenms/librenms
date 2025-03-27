<?php

require_once 'includes/systemd-shared.inc.php';

$rrdArray = [];

foreach ($systemd_mapper as $flattened_type => $state_statuses) {
    foreach (
        $systemd_mapper[$flattened_type]
        as $state_status => $rrd_location
    ) {
        $rrdArray[$flattened_type][$state_status] = [
            'descr' => $flattened_type . '_' . $state_status,
            'rrd_location' => $rrd_location,
        ];
    }
}

require 'systemd-common.inc.php';
