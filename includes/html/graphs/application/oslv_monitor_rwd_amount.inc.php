<?php

$unit_text = 'blocks/sec';

if (isset($app->data['backend']) && $app->data['backend'] == 'cgroups') {
    $unit_text = 'bytes/sec';
}

$stats_list = [
    'rbytes' => [
        'stat' => 'rbytes',
        'descr' => 'Read',
    ],
    'wbytes' => [
        'stat' => 'wbytes',
        'descr' => 'Write',
    ],
    'dbytes' => [
        'stat' => 'dbytes',
        'descr' => 'Discard',
    ],
    'read-blocks' => [
        'stat' => 'read-blocks',
        'descr' => 'Read',
    ],
    'written-blocks' => [
        'stat' => 'written-blocks',
        'descr' => 'Written',
    ],
];

require 'oslv_monitor-common.inc.php';
