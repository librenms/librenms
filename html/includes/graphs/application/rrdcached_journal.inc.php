<?php

include 'rrdcached.inc.php';

$colours = 'pinks';

$rrd_list = array(
    array(
        'ds' => 'journal_rotate',
        'filename' => $rrd_filename,
        'descr' => 'Rotated',
    ),
    array(
        'ds' => 'journal_bytes',
        'filename' => $rrd_filename,
        'descr' => 'Bytes Written',
    )
);

require 'includes/graphs/generic_multi.inc.php';
