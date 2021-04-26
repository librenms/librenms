<?php

$name = 'postfix';
$app_id = $app['app_id'];
$scale_min = 0;
$colours = 'mixed';
$unit_text = 'Rejects';
$unitlen = 1;
$bigdescrlen = 17;
$smalldescrlen = 17;
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 15;

$rrd_filename = Rrd::name($device['hostname'], ['app', $name, $app_id]);

if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_list = [
        [
            'filename' => $rrd_filename,
            'descr'    => 'Clnt Hst Rej',
            'ds'       => 'chr',
            'colour'   => '582A72',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'Helo needs FQN',
            'ds'       => 'hcrnfqh',
            'colour'   => '28774F',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'Sndr Dmn Not Fnd',
            'ds'       => 'sardnf',
            'colour'   => 'AA6C39',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'Not Owned By User',
            'ds'       => 'sarnobu',
            'colour'   => '88CC88',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'RBL',
            'ds'       => 'bu',
            'colour'   => 'D46A6A',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'Unknown Recpt',
            'ds'       => 'raruu',
            'colour'   => 'FFD1AA',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'Inv. Helo Name',
            'ds'       => 'hcrin',
            'colour'   => '582A00',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'Sender Needs FQA',
            'ds'       => 'sarnfqa',
            'colour'   => '005439',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'Recp DNF',
            'ds'       => 'rardnf',
            'colour'   => '28006C',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'Recp Needs FQA',
            'ds'       => 'rarnfqa',
            'colour'   => '00536C',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'Imp. Pipelining',
            'ds'       => 'iuscp',
            'colour'   => '853A6C',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'Config Error',
            'ds'       => 'sce',
            'colour'   => '005300',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'Config Problem',
            'ds'       => 'scp',
            'colour'   => '28006C',
        ],
        [
            'filename' => $rrd_filename,
            'descr'    => 'Unknown',
            'ds'       => 'urr',
            'colour'   => '280090',
        ],
    ];
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
