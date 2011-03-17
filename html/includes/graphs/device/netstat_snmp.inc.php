<?php

include("includes/graphs/common.inc.php");

$device = device_by_id_cache($id);

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/netstats-snmp.rrd";

$rrd_options .= " DEF:snmpInTraps=$rrd_filename:snmpInTraps:AVERAGE";
$rrd_options .= " DEF:snmpOutTraps=$rrd_filename:snmpOutTraps:AVERAGE";
$rrd_options .= " DEF:snmpInTotalReqVars=$rrd_filename:snmpInTotalReqVars:AVERAGE";
$rrd_options .= " DEF:snmpInTotalSetVars=$rrd_filename:snmpInTotalSetVars:AVERAGE";
$rrd_options .= " DEF:snmpOutGetResponses=$rrd_filename:snmpOutGetResponses:AVERAGE";
$rrd_options .= " DEF:snmpOutSetRequests=$rrd_filename:snmpOutSetRequests:AVERAGE";

$rrd_options .= " DEF:snmpInTraps_max=$rrd_filename:snmpInTraps:MAX";
$rrd_options .= " DEF:snmpOutTraps_max=$rrd_filename:snmpOutTraps:MAX";
$rrd_options .= " DEF:snmpInTotalReqVars_max=$rrd_filename:snmpInTotalReqVars:MAX";
$rrd_options .= " DEF:snmpInTotalSetVars_max=$rrd_filename:snmpInTotalSetVars:MAX";
$rrd_options .= " DEF:snmpOutGetResponses_max=$rrd_filename:snmpOutGetResponses:MAX";
$rrd_options .= " DEF:snmpOutSetRequests_max=$rrd_filename:snmpOutSetRequests:MAX";

$rrd_options .= " COMMENT:Packets/sec\ \ \ \ \ \ \ \ Current\ \ Average\ \ Maximum\\\\n";
$rrd_options .= " LINE1.25:snmpInTraps#00cc00:InTraps ";
$rrd_options .= " GPRINT:snmpInTraps:LAST:\ \ \ \ \ \ \ \ %6.2lf%s";
$rrd_options .= " GPRINT:snmpInTraps:AVERAGE:%6.2lf%s";
$rrd_options .= " GPRINT:snmpInTraps_max:MAX:%6.2lf%s\\\\n";
$rrd_options .= " LINE1.25:snmpOutTraps#006600:OutTraps    ";
$rrd_options .= " GPRINT:snmpOutTraps:LAST:\ \ \ \ \ \ \ %6.2lf%s";
$rrd_options .= " GPRINT:snmpOutTraps:AVERAGE:%6.2lf%s";
$rrd_options .= " GPRINT:snmpOutTraps_max:MAX:%6.2lf%s\\\\n";
$rrd_options .= " LINE1.25:snmpInTotalReqVars#cc0000:InTotalReqVars   ";
$rrd_options .= " GPRINT:snmpInTotalReqVars:LAST:\ %6.2lf%s";
$rrd_options .= " GPRINT:snmpInTotalReqVars:AVERAGE:%6.2lf%s";
$rrd_options .= " GPRINT:snmpInTotalReqVars_max:MAX:%6.2lf%s\\\\n";
$rrd_options .= " LINE1.25:snmpInTotalSetVars#660000:InTotalSetVars  ";
$rrd_options .= " GPRINT:snmpInTotalSetVars:LAST:\ %6.2lf%s";
$rrd_options .= " GPRINT:snmpInTotalSetVars:AVERAGE:%6.2lf%s";
$rrd_options .= " GPRINT:snmpInTotalSetVars_max:MAX:%6.2lf%s\\\\n";
$rrd_options .= " LINE1.25:snmpOutGetResponses#0066cc:OutGetResponses    ";
$rrd_options .= " GPRINT:snmpOutGetResponses:LAST:%6.2lf%s";
$rrd_options .= " GPRINT:snmpOutGetResponses:AVERAGE:%6.2lf%s";
$rrd_options .= " GPRINT:snmpOutGetResponses_max:MAX:%6.2lf%s\\\\n";
$rrd_options .= " LINE1.25:snmpOutSetRequests#003399:OutSetResponses   ";
$rrd_options .= " GPRINT:snmpOutSetRequests:LAST:%6.2lf%s";
$rrd_options .= " GPRINT:snmpOutSetRequests:AVERAGE:%6.2lf%s";
$rrd_options .= " GPRINT:snmpOutSetRequests_max:MAX:%6.2lf%s\\\\n";

?>