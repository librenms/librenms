<?php

require 'includes/graphs/common.inc.php';

$rrd_filename = rrd_name($device['hostname'], array('app', 'mysql', $app['app_id'], 'status'));

$array = array(
    'State_closing_tables'       => 'd2',
    'State_copying_to_tmp_table' => 'd3',
    'State_end'                  => 'd4',
    'State_freeing_items'        => 'd5',
    'State_init'                 => 'd6',
    'State_locked'               => 'd7',
    'State_login'                => 'd8',
    'State_preparing'            => 'd9',
    'State_reading_from_net'     => 'da',
    'State_sending_data'         => 'db',
    'State_sorting_result'       => 'dc',
    'State_statistics'           => 'dd',
    'State_updating'             => 'de',
    'State_writing_to_net'       => 'df',
    'State_none'                 => 'dg',
    'State_other'                => 'dh',
);

$i = 0;
if (is_file($rrd_filename)) {
    foreach ($array as $vars => $ds) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        if (is_array($vars)) {
            $rrd_list[$i]['descr'] = $vars['descr'];
        }
        else {
            $rrd_list[$i]['descr'] = $vars;
        }

        $rrd_list[$i]['descr'] = str_replace('_', ' ', $rrd_list[$i]['descr']);
        $rrd_list[$i]['descr'] = str_replace('State ', '', $rrd_list[$i]['descr']);
        $rrd_list[$i]['ds']    = $ds;
        $i++;
    }
}
else {
    echo "file missing: $file";
}

$colours   = 'mixed';
$nototal   = 1;
$unit_text = 'activity';

require 'includes/graphs/generic_multi_simplex_seperated.inc.php';
