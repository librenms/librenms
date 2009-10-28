<?php

include("common.inc.php");

$rrd_filename = $config['rrd_dir'] . "/" . $hostname . "/netinfo.rrd";

$rrd_options .= " DEF:ipForwDatagrams=$rrd_filename:ipForwDatagrams:AVERAGE";
$rrd_options .= " DEF:ipInDelivers=$rrd_filename:ipInDelivers:AVERAGE";
$rrd_options .= " DEF:ipInReceives=$rrd_filename:ipInReceives:AVERAGE";
$rrd_options .= " DEF:ipOutRequests=$rrd_filename:ipOutRequests:AVERAGE";
$rrd_options .= " DEF:ipInDiscards=$rrd_filename:ipInDiscards:AVERAGE";
$rrd_options .= " DEF:ipOutDiscards=$rrd_filename:ipOutDiscards:AVERAGE";
$rrd_options .= " DEF:ipOutNoRoutes=$rrd_filename:ipInDiscards:AVERAGE";

$rrd_options .= " DEF:MipReasmReqds=$database:ipReasmReqds:MAX";
$rrd_options .= " DEF:MipReasmOKs=$database:ipReasmOKs:MAX";
$rrd_options .= " DEF:MipReasmFails=$database:ipReasmFails:MAX";
$rrd_options .= " DEF:MipFragOKs=$database:ipFragOKs:MAX";
$rrd_options .= " DEF:MipFragFails=$database:ipFragFails:MAX";
$rrd_options .= " DEF:MipFragCreates=$database:ipFragCreates:MAX";
# express all as a % of ipInDelivers
# averages
$rrd_options .= " CDEF:ReasmReqds=ipReasmReqds,ipInDelivers,/,100,*";
$rrd_options .= " CDEF:ReasmOKs=ipReasmOKs,ipInDelivers,/,100,*";
$rrd_options .= " CDEF:ReasmFails=ipReasmFails,ipInDelivers,/,100,*";
$rrd_options .= " CDEF:FragOKs=ipFragOKs,ipInDelivers,/,100,*";
$rrd_options .= " CDEF:FragFails=ipFragFails,ipInDelivers,/,100,*";
$rrd_options .= " CDEF:FragCreates=ipFragCreates,ipInDelivers,/,100,*";
# maximums
$rrd_options .= " CDEF:MReasmReqds=MipReasmReqds,ipInDelivers,/,100,*";
$rrd_options .= " CDEF:MReasmOKs=MipReasmOKs,ipInDelivers,/,100,*";
$rrd_options .= " CDEF:MReasmFails=MipReasmFails,ipInDelivers,/,100,*";
$rrd_options .= " CDEF:MFragOKs=MipFragOKs,ipInDelivers,/,100,*";
$rrd_options .= " CDEF:MFragFails=MipFragFails,ipInDelivers,/,100,*";
$rrd_options .= " CDEF:MFragCreates=MipFragCreates,ipInDelivers,/,100,*";
# print some lines, with fails stacked on top
$rrd_options .= " LINE1:FragOKs#00ff00:Fragmentation OK";
$rrd_options .= " LINE2:FragFails#ff0000:Fragmentation Fail";
$rrd_options .= " LINE1:ReasmOKs#0033aa:Reassembly OK";
$rrd_options .= " LINE2:ReasmFails#000000:Reassembly Fail";
# print some summary numbers.
$rrd_options .= " GPRINT:ReasmReqds:AVERAGE:Avg ReasmReqd %1.2lf %%";
$rrd_options .= " GPRINT:MReasmReqds:MAX:Max ReasmReqd %1.2lf %%";
$rrd_options .= " GPRINT:ReasmOKs:AVERAGE:Avg ReasmOK %1.2lf %%";
$rrd_options .= " GPRINT:MReasmOKs:MAX:Max ReasmOK %1.2lf %%";
$rrd_options .= " GPRINT:ReasmFails:AVERAGE:Avg ReasmFail %1.2lf %%";
$rrd_options .= " GPRINT:MReasmFails:MAX:Max ReasmFail %1.2lf %%";
$rrd_options .= " GPRINT:FragOKs:AVERAGE:Avg FragOK %1.2lf %%";
$rrd_options .= " GPRINT:MFragOKs:MAX:Max FragOK %1.2lf %%";
$rrd_options .= " GPRINT:FragFails:AVERAGE:Avg FragFail %1.2lf %%";
$rrd_options .= " GPRINT:MFragFails:MAX:Max FragFail %1.2lf %%";
$rrd_options .= " GPRINT:FragCreates:AVERAGE:Avg FragCreate %1.2lf %%";
$rrd_options .= " GPRINT:MFragCreates:MAX:Max FragCreate %1.2lf %%";
$rrd_options .= " COMMENT:   Calculated as a % of ipInDelivers"

?>
