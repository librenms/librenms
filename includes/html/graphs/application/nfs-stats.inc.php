<?php
require 'includes/html/graphs/common.inc.php';
$scale_min    = 0;
$colours      = 'mixed';
$unit_text    = 'Operations';
$unitlen      = 10;
$bigdescrlen  = 15;
$smalldescrlen = 15;
$dostack      = 0;
$printtotal      = 0;
$rrd_filename = get_rrd_dir($device['hostname']).'/app-nfsstats-'.$app['app_id'].'.rrd';
$array        = array(
                 'total' => array(
                                    'descr'  => 'Total',
                                    'colour' => '000000',
                                   ),
                 'null' => array(
                                    'descr'  => 'NULL',
                                    'colour' => '630606',
                                   ),
                 'getattr' => array(
                                    'descr'  => 'Get attributes',
                                    'colour' => '50C150',
                                   ),
                 'setattr' => array(
                                    'descr'  => 'Set attributes',
                                    'colour' => '4D65A2',
                                   ),
                  'lookup' => array(
                                    'descr'  => 'Lookup',
                                    'colour' => '8B64A1',
                                    ),
                  'access' => array(
                                    'descr'  => 'Access',
                                    'colour' => 'AAAA39',
                                    ),
                  'read' => array(
                                    'descr'  => 'Read',
                                    'colour' => '',
                                    ),
                  'write' => array(
                                    'descr'  => 'Write',
                                    'colour' => '457A9A',
                                    ),
                  'create' => array(
                                    'descr'  => 'Create',
                                    'colour' => '690D87',
                                    ),
                  'mkdir' => array(
                                    'descr'  => 'Make dir',
                                    'colour' => '072A3F',
                                    ),
                  'remove' => array(
                                    'descr'  => 'Remove',
                                    'colour' => 'F16464',
                                  ),
                  'rmdir' => array(
                                    'descr'  => 'Remove dir',
                                    'colour' => '57162D',
                                  ),
                  'rename' => array(
                                    'descr'  => 'Rename',
                                    'colour' => 'A40B62',
                                  ),
                  'readdirplus' => array(
                                    'descr'  => 'Read dir plus',
                                    'colour' => 'F1F164',
                                  ),
                  'fsstat' => array(
                                    'descr'  => 'FS stat',
                                    'colour' => 'F1F191',
                                  ),
                );

$i = 0;

if (rrdtool_check_rrd_exists($rrd_filename)) {
    foreach ($array as $ds => $var) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr']    = $var['descr'];
        $rrd_list[$i]['ds']       = $ds;
        $rrd_list[$i]['colour']   = $var['colour'];
        $i++;
    }
} else {
    echo "file missing: $file";
}

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
