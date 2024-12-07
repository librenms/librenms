<?php

$unit_text = 'per second';

$stats_list = [
    'refault_anon' => [
        'stat' => 'workingset_refault_anon',
        'descr' => 'refault_anon',
    ],
    'refault_file' => [
        'stat' => 'workingset_refault_file',
        'descr' => 'refault_file',
    ],
    'workingset_activate_anon' => [
        'stat' => 'workingset_activate_anon',
        'descr' => 'activate_anon',
    ],
    'workingset_activate_file' => [
        'stat' => 'workingset_activate_file',
        'descr' => 'activate_file',
    ],
    'workingset_restore_anon' => [
        'stat' => 'workingset_restore_anon',
        'descr' => 'restore_anon',
    ],
    'workingset_restore_file' => [
        'stat' => 'workingset_restore_file',
        'descr' => 'restore_file',
    ],
    'workingset_nodereclaim' => [
        'stat' => 'workingset_nodereclaim',
        'descr' => 'nodereclaim',
    ],
];

require 'oslv_monitor-common.inc.php';
