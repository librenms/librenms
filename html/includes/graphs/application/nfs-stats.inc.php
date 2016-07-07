<?php

require 'includes/graphs/common.inc.php';
$scale_min    = 0;
$colours      = 'mixed';
$unit_text    = 'Operation';
$unitlen      = 9;
$rrd_filename = $config['rrd_dir'].'/'.$device['hostname'].'/app-nfsstats-'.$app['app_id'].'.rrd';
$array        = array(
                 'total' => array(
                                    'descr'  => 'Total',
                                    'colour' => '570623',
                                   ),
                 'null' => array(
                                    'descr'  => 'NULL',
                                    'colour' => 'D37F9D',
                                   ),
                 'getattr' => array(
                                    'descr'  => 'Get attributes',
                                    'colour' => 'F1B264',
                                   ),
                 'setattr' => array(
                                    'descr'  => 'Set attributes',
                                    'colour' => '634219',
                                   ),
                  'lookup' => array(
                                    'descr'  => 'Lookup',
                                    'colour' => '60849A',
                                    ),
                  'access' => array(
                                    'descr'  => 'Access',
                                    'colour' => 'AED983',
                                    ),
                  'read' => array(
                                    'descr'  => 'Read',
                                    'colour' => '9AD95A',
                                    ),
                  'write' => array(
                                    'descr'  => 'Write',
                                    'colour' => '457A9A',
                                    ),
                  'create' => array(
                                    'descr'  => 'Create',
                                    'colour' => '132E3F',
                                    ),
                  'mkdir' => array(
                                    'descr'  => 'Make dir',
                                    'colour' => '072A3F',
                                    ),
                  'remove' => array(
                                    'descr'  => 'Remove',
                                    'colour' => '570623',
                                  ),
                  'rmdir' => array(
                                    'descr'  => 'Remove dir',
                                    'colour' => '57162D',
                                  ),
                  'rename' => array(
                                    'descr'  => 'Rename',
                                    'colour' => 'AA7739',
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

if (is_file($rrd_filename)) {
    foreach ($array as $ds => $vars) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr']    = $vars['descr'];
        $rrd_list[$i]['ds']       = $ds;
        $rrd_list[$i]['colour']   = $vars['colour'];
        $i++;
    }
}
else {
    echo "file missing: $file";
}

require 'includes/graphs/generic_multi_line_exact_numbers.inc.php';
