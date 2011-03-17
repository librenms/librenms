<?php

include("includes/graphs/common.inc.php");
$device = device_by_id_cache($id);

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/netstats-snmp.rrd";

$rrd_options .= " DEF:snmpInPkts=$rrd_filename:snmpInPkts:AVERAGE";
$rrd_options .= " DEF:snmpInPkts_max=$rrd_filename:snmpInPkts:MAX";
$rrd_options .= " DEF:snmpOutPkts=$rrd_filename:snmpOutPkts:AVERAGE";
$rrd_options .= " DEF:snmpOutPkts_max=$rrd_filename:snmpOutPkts:MAX";
$rrd_options .= " CDEF:snmpOutPkts_max_neg=snmpOutPkts_max,-1,*";
$rrd_options .= " CDEF:snmpOutPkts_neg=snmpOutPkts,-1,*";
$rrd_options .= " COMMENT:Packets/sec\ \ \ \ Current\ \ \ \ Average\ \ \ Maximum\\\\n";
$rrd_options .= " LINE1.25:snmpInPkts#009900:snmpInPkts\ \ ";
$rrd_options .= " GPRINT:snmpInPkts:LAST:%6.2lf%s";
$rrd_options .= " GPRINT:snmpInPkts:AVERAGE:\ %6.2lf%s";
$rrd_options .= " GPRINT:snmpInPkts_max:MAX:\ %6.2lf%s\\\\n";
$rrd_options .= " LINE1.25:snmpOutPkts_neg#003399:snmpOutPkts\ ";
#$rrd_options .= " AREA:snmpOutPkts_max_neg#4466AA::";
$rrd_options .= " GPRINT:snmpOutPkts:LAST:%6.2lf%s";
$rrd_options .= " GPRINT:snmpOutPkts:AVERAGE:\ %6.2lf%s";
$rrd_options .= " GPRINT:snmpOutPkts_max:MAX:\ %6.2lf%s\\\\n";

?>