<?php

$scale_min = '0';

$rrd_options .= " COMMENT:'                      Cur    Min     Max\\n'";


$colours = 'blues';

if (strpos($vars['id'], ',') !== false) {
    $i = 0;
    foreach (explode(',', $vars['id']) as $id) {
        if (strstr($id, '!')) {
            $rrd_inverted[$i] = true;
            $id             = str_replace('!', '', $id);
        }

        $device = dbFetchRow('SELECT `hostname` FROM `sensors` as S, devices as D WHERE S.sensor_id = ? AND S.device_id = D.device_id', array($id));
        $sensor = dbFetchRow('SELECT * FROM sensors WHERE sensor_id = ?', array($id));
        $sensor['sensor_descr_fixed'] = rrdtool_escape($sensor['sensor_descr'], 15);
        $rrd_file = get_sensor_rrd($device, $sensor);

        if (rrdtool_check_rrd_exists($rrd_file)) {
            $rrd_list[$i] = array(
                'ds' => 'sensor',
                'filename' => $rrd_file,
                'descr' => 'clients',
            );
            $i++;
        }
    }
    require 'includes/graphs/generic_multi.inc.php';
} else {
    $sensor['sensor_descr_fixed'] = rrdtool_escape($sensor['sensor_descr'], 15);

    if (is_numeric($sensor['sensor_limit'])) {
        $rrd_options .= ' HRULE:'.$sensor['sensor_limit'].'#999999::dashes';
    }

    if (is_numeric($sensor['sensor_limit_low'])) {
        $rrd_options .= ' HRULE:'.$sensor['sensor_limit_low'].'#999999::dashes';
    }
    $rrd_options .= " DEF:sensor=$rrd_filename:sensor:LAST";
    $rrd_options .= " DEF:sensor_min=$rrd_filename:sensor:MIN";
    $rrd_options .= " DEF:sensor_max=$rrd_filename:sensor:MAX";
    $rrd_options .= " LINE1.5:sensor#cc0000:'".$sensor['sensor_descr_fixed']."'";
    $rrd_options .= ' GPRINT:sensor:LAST:%4lg';
    $rrd_options .= " GPRINT:sensor_min$current_id:MIN:%4lg";
    $rrd_options .= ' GPRINT:sensor_max:MAX:%4lg\\l';
    require 'includes/graphs/common.inc.php';
}
