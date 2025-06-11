<?php

$name = 'hv-monitor';
$unit_text = 'VM Statuses';
$colours = 'psychedelic';
$dostack = 0;
$printtotal = 0;
$addarea = 0;
$transparency = 15;

if (isset($vars['vm'])) {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id, 'vm', $vars['vm']]);
} else {
    $rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app->app_id]);
}

$rrd_list = [
[
        'filename' => $rrd_filename,
        'descr' => 'On',
        'ds' => 'on',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Off',
        'ds' => 'off',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Off, Hard',
        'ds' => 'off_hard',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Off, Soft',
        'ds' => 'off_soft',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Unknown',
        'ds' => 'unknown',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Paused',
        'ds' => 'paused',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Crashed',
        'ds' => 'crashed',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'Blocked',
        'ds' => 'blocked',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'No State',
        'ds' => 'nostate',
    ],
[
        'filename' => $rrd_filename,
        'descr' => 'PM Suspended',
        'ds' => 'pmsuspended',
    ],
];


require 'includes/html/graphs/generic_multi_line.inc.php';
