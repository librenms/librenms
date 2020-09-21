<?php

// provide some sane default
if ($service['service_param']) {
    $dbname = $service['service_param'];
} else {
    $dbname = 'mysql';
}
$check_cmd = \LibreNMS\Config::get('nagios_plugins') . '/check_mysql -H ' . $service['hostname'] . ' ' . $dbname . ' ' . $service['service_param'];

// Check DS is a json array of the graphs that are available
$check_ds = '{"mysqlqueries":"c","mysql":"c","mysqluptime":"c","mysqlQcache":"c"}';

// Build the graph data
$check_graph = [];
$mixed_colours = \LibreNMS\Config::get('graph_colours.mixed');

$check_graph['mysqlqueries'] = ' DEF:DS0=' . $rrd_filename . ':Queries:AVERAGE ';
$check_graph['mysqlqueries'] .= ' LINE1.25:DS0#' . $mixed_colours[1] . ":'" . str_pad(substr('Queries', 0, 19), 19) . "' ";
$check_graph['mysqlqueries'] .= ' GPRINT:DS0:LAST:%0.0lf ';
$check_graph['mysqlqueries'] .= ' GPRINT:DS0:AVERAGE:%0.0lf ';
$check_graph['mysqlqueries'] .= ' GPRINT:DS0:MAX:%0.0lf\\l ';
$check_graph['mysqlqueries'] .= ' DEF:DS1=' . $rrd_filename . ':Questions:AVERAGE ';
$check_graph['mysqlqueries'] .= ' LINE1.25:DS1#' . $mixed_colours[2] . ":'" . str_pad(substr('Questions', 0, 19), 19) . "' ";
$check_graph['mysqlqueries'] .= ' GPRINT:DS1:LAST:%0.0lf ';
$check_graph['mysqlqueries'] .= ' GPRINT:DS1:AVERAGE:%0.0lf ';
$check_graph['mysqlqueries'] .= ' GPRINT:DS1:MAX:%0.0lf\\l ';

$check_graph['mysql'] = ' DEF:DS0=' . $rrd_filename . ':Connections:AVERAGE ';
$check_graph['mysql'] .= ' LINE1.25:DS0#' . $mixed_colours[0] . ":'" . str_pad(substr('Connections', 0, 19), 19) . "' ";
$check_graph['mysql'] .= ' GPRINT:DS0:LAST:%5.2lf%s ';
$check_graph['mysql'] .= ' GPRINT:DS0:AVERAGE:%5.2lf%s ';
$check_graph['mysql'] .= ' GPRINT:DS0:MAX:%5.2lf%s\\l ';
$check_graph['mysql'] .= ' DEF:DS1=' . $rrd_filename . ':Open_files:AVERAGE ';
$check_graph['mysql'] .= ' LINE1.25:DS1#' . $mixed_colours[3] . ":'" . str_pad(substr('Open_files', 0, 19), 19) . "' ";
$check_graph['mysql'] .= ' GPRINT:DS1:LAST:%0.0lf ';
$check_graph['mysql'] .= ' GPRINT:DS1:AVERAGE:%0.0lf ';
$check_graph['mysql'] .= ' GPRINT:DS1:MAX:%0.0lf\\l ';
$check_graph['mysql'] .= ' DEF:DS2=' . $rrd_filename . ':Open_tables:AVERAGE ';
$check_graph['mysql'] .= ' LINE1.25:DS2#' . $mixed_colours[4] . ":'" . str_pad(substr('Open_tables', 0, 19), 19) . "' ";
$check_graph['mysql'] .= ' GPRINT:DS2:LAST:%0.0lf ';
$check_graph['mysql'] .= ' GPRINT:DS2:AVERAGE:%0.0lf ';
$check_graph['mysql'] .= ' GPRINT:DS2:MAX:%0.0lf\\l ';
$check_graph['mysql'] .= ' DEF:DS3=' . $rrd_filename . ':Table_locks_waited:AVERAGE ';
$check_graph['mysql'] .= ' LINE1.25:DS3#' . $mixed_colours[5] . ":'" . str_pad(substr('Table_locks_waited', 0, 19), 19) . "' ";
$check_graph['mysql'] .= ' GPRINT:DS3:LAST:%0.0lf ';
$check_graph['mysql'] .= ' GPRINT:DS3:AVERAGE:%0.0lf ';
$check_graph['mysql'] .= ' GPRINT:DS3:MAX:%0.0lf\\l ';
$check_graph['mysql'] .= ' DEF:DS4=' . $rrd_filename . ':Threads_connected:AVERAGE ';
$check_graph['mysql'] .= ' LINE1.25:DS4#' . $mixed_colours[6] . ":'" . str_pad(substr('Threads_connected', 0, 19), 19) . "' ";
$check_graph['mysql'] .= ' GPRINT:DS4:LAST:%0.0lf ';
$check_graph['mysql'] .= ' GPRINT:DS4:AVERAGE:%0.0lf ';
$check_graph['mysql'] .= ' GPRINT:DS4:MAX:%0.0lf\\l ';
$check_graph['mysql'] .= ' DEF:DS5=' . $rrd_filename . ':Threads_running:AVERAGE ';
$check_graph['mysql'] .= ' LINE1.25:DS5#' . $mixed_colours[7] . ":'" . str_pad(substr('Threads_running', 0, 19), 19) . "' ";
$check_graph['mysql'] .= ' GPRINT:DS5:LAST:%0.0lf ';
$check_graph['mysql'] .= ' GPRINT:DS5:AVERAGE:%0.0lf ';
$check_graph['mysql'] .= ' GPRINT:DS5:MAX:%0.0lf\\l ';

$check_graph['mysqluptime'] = ' DEF:DS0=' . $rrd_filename . ':Uptime:LAST ';
$check_graph['mysqluptime'] .= ' CDEF:cuptime=DS0,86400,/';
$check_graph['mysqluptime'] .= " 'COMMENT:Days      Current  Minimum  Maximum  Average\\n'";
$check_graph['mysqluptime'] .= ' AREA:cuptime#EEEEEE:Uptime';
$check_graph['mysqluptime'] .= ' LINE1.25:cuptime#36393D:';
$check_graph['mysqluptime'] .= ' GPRINT:cuptime:LAST:%6.2lf  GPRINT:cuptime:MIN:%6.2lf';
$check_graph['mysqluptime'] .= ' GPRINT:cuptime:MAX:%6.2lf  GPRINT:cuptime:AVERAGE:%6.2lf\\l';

$check_graph['mysqlQcache'] = ' DEF:DS0=' . $rrd_filename . ':Qcache_free_memory:AVERAGE ';
$check_graph['mysqlQcache'] .= ' LINE1.25:DS0#' . $mixed_colours[0] . ":'" . str_pad(substr('Qcache_free_memory', 0, 19), 19) . "' ";
$check_graph['mysqlQcache'] .= ' GPRINT:DS0:LAST:%9.2lf%s ';
$check_graph['mysqlQcache'] .= ' GPRINT:DS0:AVERAGE:%9.2lf%s ';
$check_graph['mysqlQcache'] .= ' GPRINT:DS0:MAX:%9.2lf%s\\l ';
$check_graph['mysqlQcache'] .= ' DEF:DS1=' . $rrd_filename . ':Qcache_hits:AVERAGE ';
$check_graph['mysqlQcache'] .= ' LINE1.25:DS1#' . $mixed_colours[1] . ":'" . str_pad(substr('Qcache_hits', 0, 19), 19) . "' ";
$check_graph['mysqlQcache'] .= ' GPRINT:DS1:LAST:%9.2lf ';
$check_graph['mysqlQcache'] .= ' GPRINT:DS1:AVERAGE:%9.2lf ';
$check_graph['mysqlQcache'] .= ' GPRINT:DS1:MAX:%9.2lf\\l ';
$check_graph['mysqlQcache'] .= ' DEF:DS2=' . $rrd_filename . ':Qcache_inserts:AVERAGE ';
$check_graph['mysqlQcache'] .= ' LINE1.25:DS2#' . $mixed_colours[2] . ":'" . str_pad(substr('Qcache_inserts', 0, 19), 19) . "' ";
$check_graph['mysqlQcache'] .= ' GPRINT:DS2:LAST:%9.2lf ';
$check_graph['mysqlQcache'] .= ' GPRINT:DS2:AVERAGE:%9.2lf ';
$check_graph['mysqlQcache'] .= ' GPRINT:DS2:MAX:%9.2lf\\l ';
$check_graph['mysqlQcache'] .= ' DEF:DS3=' . $rrd_filename . ':Qcache_lowmem_prune:AVERAGE ';
$check_graph['mysqlQcache'] .= ' LINE1.25:DS3#' . $mixed_colours[3] . ":'" . str_pad(substr('Qcache_lowmem_prune', 0, 19), 19) . "' ";
$check_graph['mysqlQcache'] .= ' GPRINT:DS3:LAST:%9.2lf ';
$check_graph['mysqlQcache'] .= ' GPRINT:DS3:AVERAGE:%9.2lf ';
$check_graph['mysqlQcache'] .= ' GPRINT:DS3:MAX:%9.2lf\\l ';
$check_graph['mysqlQcache'] .= ' DEF:DS4=' . $rrd_filename . ':Qcache_not_cached:AVERAGE ';
$check_graph['mysqlQcache'] .= ' LINE1.25:DS4#' . $mixed_colours[4] . ":'" . str_pad(substr('Qcache_not_cached', 0, 19), 19) . "' ";
$check_graph['mysqlQcache'] .= ' GPRINT:DS4:LAST:%9.2lf ';
$check_graph['mysqlQcache'] .= ' GPRINT:DS4:AVERAGE:%9.2lf ';
$check_graph['mysqlQcache'] .= ' GPRINT:DS4:MAX:%9.2lf\\l ';
$check_graph['mysqlQcache'] .= ' DEF:DS5=' . $rrd_filename . ':Qcache_queries_in_c:AVERAGE ';
$check_graph['mysqlQcache'] .= ' LINE1.25:DS5#' . $mixed_colours[5] . ":'" . str_pad(substr('Qcache_queries_in_c', 0, 19), 19) . "' ";
$check_graph['mysqlQcache'] .= ' GPRINT:DS5:LAST:%9.2lf ';
$check_graph['mysqlQcache'] .= ' GPRINT:DS5:AVERAGE:%9.2lf ';
$check_graph['mysqlQcache'] .= ' GPRINT:DS5:MAX:%9.2lf\\l ';
