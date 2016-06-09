<?php

include 'rrdcached.inc.php';

$nototal = 1;
$colours = 'mixed';

$rrd_list = array(
    array (
        'ds' => 'updates_written',
        'filename' => $rrd_filename,
        'descr' => 'Updates Written',
    ),
    array (
        'ds' => 'data_sets_written',
        'filename' => $rrd_filename,
        'descr' => 'Data Sets Written',
    ),
    array(
        'ds' => 'updates_received',
        'filename' => $rrd_filename,
        'descr' => 'Updates Recieved',
    ),
    array (
        'ds' => 'flushes_received',
        'filename' => $rrd_filename,
        'descr' => 'Flushes Recieved',
    ),
);

require 'includes/graphs/generic_multi_line.inc.php';
