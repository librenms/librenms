<?php

// check_cmd is the command that is run to execute the check
$check_cmd = \LibreNMS\Config::get('nagios_plugins') . '/check_icmp ' . ($service['service_ip'] ? $service['service_ip'] : $service['hostname']) . ' ' . $service['service_param'];

// Check DS is a json array of the graphs that are available
$check_ds = '{"rtt":"s","pl":"%"}';

// Build the graph data
$check_graph = [];
$check_graph['rtt'] = ' DEF:DS0=' . $rrd_filename . ':rta:AVERAGE ';
$check_graph['rtt'] .= ' LINE1.25:DS0#' . \LibreNMS\Config::get('graph_colours.mixed.0') . ":'" . str_pad(substr('Round Trip Average', 0, 15), 15) . "' ";
$check_graph['rtt'] .= ' GPRINT:DS0:LAST:%5.2lf%s ';
$check_graph['rtt'] .= ' GPRINT:DS0:AVERAGE:%5.2lf%s ';
$check_graph['rtt'] .= ' GPRINT:DS0:MAX:%5.2lf%s\\l ';
$check_graph['rtt'] .= ' DEF:DS1=' . $rrd_filename . ':rtmax:AVERAGE ';
$check_graph['rtt'] .= ' LINE1.25:DS1#' . \LibreNMS\Config::get('graph_colours.mixed.1') . ":'" . str_pad(substr('Round Trip Max', 0, 15), 15) . "' ";
$check_graph['rtt'] .= ' GPRINT:DS1:LAST:%5.2lf%s ';
$check_graph['rtt'] .= ' GPRINT:DS1:AVERAGE:%5.2lf%s ';
$check_graph['rtt'] .= ' GPRINT:DS1:MAX:%5.2lf%s\\l ';
$check_graph['rtt'] .= ' DEF:DS2=' . $rrd_filename . ':rtmin:AVERAGE ';
$check_graph['rtt'] .= ' LINE1.25:DS2#' . \LibreNMS\Config::get('graph_colours.mixed.2') . ":'" . str_pad(substr('Round Trip Min', 0, 15), 15) . "' ";
$check_graph['rtt'] .= ' GPRINT:DS2:LAST:%5.2lf%s ';
$check_graph['rtt'] .= ' GPRINT:DS2:AVERAGE:%5.2lf%s ';
$check_graph['rtt'] .= ' GPRINT:DS2:MAX:%5.2lf%s\\l ';

$check_graph['pl'] = ' DEF:DS0=' . $rrd_filename . ':pl:AVERAGE ';
$check_graph['pl'] .= ' AREA:DS0#' . \LibreNMS\Config::get('graph_colours.mixed.2') . ":'" . str_pad(substr('Packet Loss (%)', 0, 15), 15) . "' ";
$check_graph['pl'] .= ' GPRINT:DS0:LAST:%5.2lf%s ';
$check_graph['pl'] .= ' GPRINT:DS0:AVERAGE:%5.2lf%s ';
$check_graph['pl'] .= ' GPRINT:DS0:MAX:%5.2lf%s\\l ';
