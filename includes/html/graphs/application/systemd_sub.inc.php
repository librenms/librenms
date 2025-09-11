<?php

require_once 'includes/systemd-shared.inc.php';

$rrdArray = [];
$state_type = 'sub';

if (isset($vars['sub_state_type'])) {
    // This section draws the individual graphs in the device application page
    // displaying the SPECIFIED service type's sub states.
    $flattened_type = $vars['sub_state_type'];

    foreach (
        $systemd_mapper[$flattened_type]
        as $sub_state_status => $rrd_location
    ) {
        $rrdArray[$flattened_type][$sub_state_status] = [
            'descr' => $sub_state_status,
            'rrd_location' => $rrd_location,
        ];
    }
} else {
    // This section draws the graph for the application-specific pages
    // displaying ALL of the service type's sub states.
    foreach ($systemd_mapper as $flattened_type => $state_statuses) {
        // Ternary-depth systemd type check.
        if (! preg_match('/^(.+)_(.+)$/', $flattened_type, $regex_matches)) {
            continue;
        }
        if ($regex_matches[1] !== $state_type) {
            continue;
        }

        foreach (
            $systemd_mapper[$flattened_type]
            as $sub_state_status => $rrd_location
        ) {
            $rrdArray[$flattened_type][$sub_state_status] = [
                'descr' => $flattened_type . '_' . $sub_state_status,
                'rrd_location' => $rrd_location,
            ];
        }
    }
}

require 'systemd-common.inc.php';
