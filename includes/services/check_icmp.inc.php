<?php

// check_cmd is the command that is run to execute the check
$check_cmd = \App\Facades\LibrenmsConfig::get('nagios_plugins') . '/check_icmp ' . $service['service_param'] . ' ' . ($service['service_ip'] ? $service['service_ip'] : $service['hostname']);

if (isset($rrd_filename)) {
    // Check DS is a json array of the graphs that are available
    $check_ds = '{"rta":"s","rtmax":"s","rtmin":"s","pl":"%"}';

    // Build the graph data
    $check_graph = [];
    $check_graph['rta'] = ' DEF:DS0=' . $rrd_filename . ':rta:AVERAGE ';
    $check_graph['rta'] .= ' LINE1.25:DS0#' . \App\Facades\LibrenmsConfig::get('graph_colours.mixed.0') . ":'" . str_pad(substr('Round Trip Average', 0, 15), 15) . "' ";
    $check_graph['rta'] .= ' GPRINT:DS0:LAST:%5.2lf%s ';
    $check_graph['rta'] .= ' GPRINT:DS0:AVERAGE:%5.2lf%s ';
    $check_graph['rta'] .= ' GPRINT:DS0:MAX:%5.2lf%s\\l ';
    $check_graph['rtmax'] .= ' DEF:DS1=' . $rrd_filename . ':rtmax:AVERAGE ';
    $check_graph['rtmax'] .= ' LINE1.25:DS1#' . \App\Facades\LibrenmsConfig::get('graph_colours.mixed.1') . ":'" . str_pad(substr('Round Trip Max', 0, 15), 15) . "' ";
    $check_graph['rtmax'] .= ' GPRINT:DS1:LAST:%5.2lf%s ';
    $check_graph['rtmax'] .= ' GPRINT:DS1:AVERAGE:%5.2lf%s ';
    $check_graph['rtmax'] .= ' GPRINT:DS1:MAX:%5.2lf%s\\l ';
    $check_graph['rtmin'] .= ' DEF:DS2=' . $rrd_filename . ':rtmin:AVERAGE ';
    $check_graph['rtmin'] .= ' LINE1.25:DS2#' . \App\Facades\LibrenmsConfig::get('graph_colours.mixed.2') . ":'" . str_pad(substr('Round Trip Min', 0, 15), 15) . "' ";
    $check_graph['rtmin'] .= ' GPRINT:DS2:LAST:%5.2lf%s ';
    $check_graph['rtmin'] .= ' GPRINT:DS2:AVERAGE:%5.2lf%s ';
    $check_graph['rtmin'] .= ' GPRINT:DS2:MAX:%5.2lf%s\\l ';

    $check_graph['pl'] = ' DEF:DS0=' . $rrd_filename . ':pl:AVERAGE ';
    $check_graph['pl'] .= ' AREA:DS0#' . \App\Facades\LibrenmsConfig::get('graph_colours.mixed.2') . ":'" . str_pad(substr('Packet Loss (%)', 0, 15), 15) . "' ";
    $check_graph['pl'] .= ' GPRINT:DS0:LAST:%5.2lf%s ';
    $check_graph['pl'] .= ' GPRINT:DS0:AVERAGE:%5.2lf%s ';
    $check_graph['pl'] .= ' GPRINT:DS0:MAX:%5.2lf%s\\l ';
}
