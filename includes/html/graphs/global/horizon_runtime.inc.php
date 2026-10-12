<?php

use App\Horizon\DatastoreMetricsRepository;

$jobs = [
    'PollDevice' => 'Poll',
    'DiscoverDevice' => 'Discovery',
    'DispatchPollingWork' => 'Dispatcher',
];

$rrd_list = [];
foreach ($jobs as $job => $descr) {
    $rrd_filename = Rrd::name(DatastoreMetricsRepository::HOSTNAME, ['horizon', 'job', $job]);
    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr' => $descr,
            'ds' => 'runtime',
        ];
    }
}

$unit_text = 'Seconds';
$colours = 'mixed';
$scale_min = '0';
$nototal = 1;
$float_precision = 2;
$divider = 1000; // milliseconds

include 'includes/html/graphs/generic_multi_line.inc.php';
