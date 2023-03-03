<?php

require_once 'includes/systemd-shared.inc.php';

$rrdArray = [];

// This pulls the service sub state type
// given by the opened page.  Otherwise, 'service'
// is the default for the Application overview
// pages.
$sub_state_type = $vars['sub_state_type'] ?? 'service';

$sub_flattened_name = 'sub_' . $sub_state_type;

foreach ($systemd_mapper['sub'][$sub_state_type] as $sub_state_status) {
    $rrdArray[$sub_flattened_name][$sub_state_status] = [
        'descr' => $sub_state_status,
    ];
}

require 'systemd-common.inc.php';
