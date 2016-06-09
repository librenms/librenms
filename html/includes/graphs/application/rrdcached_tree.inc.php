<?php

include 'rrdcached.inc.php';

$colours = 'blues';

$rrd_list = array(
    array(
        'ds' => 'tree_depth',
        'filename' => $rrd_filename,
        'descr' => 'Depth',
    ),
    array(
        'ds' => 'tree_nodes_number',
        'filename' => $rrd_filename,
        'descr' => 'Nodes',
    )
);

require 'includes/graphs/generic_multi.inc.php';
