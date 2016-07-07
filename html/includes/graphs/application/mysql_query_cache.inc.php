<?php

require 'includes/graphs/common.inc.php';

$mysql_rrd = rrd_name($device['hostname'], array('app', mysql, $app['app_id']));

if (is_file($mysql_rrd)) {
    $rrd_filename = $mysql_rrd;
}

$array = array(
          'QCQICe' => array(
                       'descr'  => 'Queries in cache',
                       'colour' => '22FF22',
                      ),
          'QCHs'   => array(
                       'descr'  => 'Cache hits',
                       'colour' => '0022FF',
                      ),
          'QCIs'   => array(
                       'descr'  => 'Inserts',
                       'colour' => 'FF0000',
                      ),
          'QCNCd'  => array(
                       'descr'  => 'Not cached',
                       'colour' => '00AAAA',
                      ),
          'QCLMPs' => array(
                       'descr'  => 'Low-memory prunes',
                       'colour' => 'FF00FF',
                      ),
         );

$i = 0;
if (is_file($rrd_filename)) {
    foreach ($array as $ds => $vars) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr']    = $vars['descr'];
        $rrd_list[$i]['ds']       = $ds;
        // $rrd_list[$i]['colour'] = $vars['colour'];
        $i++;
    }
}
else {
    echo "file missing: $file";
}

$colours   = 'mixed';
$nototal   = 1;
$unit_text = 'Commands';

require 'includes/graphs/generic_multi_simplex_seperated.inc.php';
