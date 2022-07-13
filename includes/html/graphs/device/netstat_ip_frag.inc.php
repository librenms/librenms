<?php

require 'includes/html/graphs/common.inc.php';

$rrd_filename = Rrd::name($device['hostname'], 'netstats-ip');

$rrd_options .= " DEF:ipInDelivers=$rrd_filename:ipInDelivers:AVERAGE";
$rrd_options .= " DEF:ipReasmReqds=$rrd_filename:ipReasmReqds:AVERAGE";
$rrd_options .= " DEF:ipReasmOKs=$rrd_filename:ipReasmOKs:AVERAGE";
$rrd_options .= " DEF:ipReasmFails=$rrd_filename:ipReasmFails:AVERAGE";
$rrd_options .= " DEF:ipFragOKs=$rrd_filename:ipFragOKs:AVERAGE";
$rrd_options .= " DEF:ipFragFails=$rrd_filename:ipFragFails:AVERAGE";
$rrd_options .= " DEF:ipFragCreates=$rrd_filename:ipFragCreates:AVERAGE";
$rrd_options .= " DEF:MipReasmReqds=$rrd_filename:ipReasmReqds:MAX";
$rrd_options .= " DEF:MipReasmOKs=$rrd_filename:ipReasmOKs:MAX";
$rrd_options .= " DEF:MipReasmFails=$rrd_filename:ipReasmFails:MAX";
$rrd_options .= " DEF:MipFragOKs=$rrd_filename:ipFragOKs:MAX";
$rrd_options .= " DEF:MipFragFails=$rrd_filename:ipFragFails:MAX";
$rrd_options .= " DEF:MipFragCreates=$rrd_filename:ipFragCreates:MAX";
$rrd_options .= ' CDEF:ReasmReqds=ipReasmReqds,ipInDelivers,/,100,*';
$rrd_options .= ' CDEF:ReasmOKs=ipReasmOKs,ipInDelivers,/,100,*';
$rrd_options .= ' CDEF:ReasmFails=ipReasmFails,ipInDelivers,/,100,*';
$rrd_options .= ' CDEF:FragOKs=ipFragOKs,ipInDelivers,/,100,*';
$rrd_options .= ' CDEF:FragFails=ipFragFails,ipInDelivers,/,100,*';
$rrd_options .= ' CDEF:FragCreates=ipFragCreates,ipInDelivers,/,100,*';
$rrd_options .= ' CDEF:MReasmReqds=MipReasmReqds,ipInDelivers,/,100,*';
$rrd_options .= ' CDEF:MReasmOKs=MipReasmOKs,ipInDelivers,/,100,*';
$rrd_options .= ' CDEF:MReasmFails=MipReasmFails,ipInDelivers,/,100,*';
$rrd_options .= ' CDEF:MFragOKs=MipFragOKs,ipInDelivers,/,100,*';
$rrd_options .= ' CDEF:MFragFails=MipFragFails,ipInDelivers,/,100,*';
$rrd_options .= ' CDEF:MFragCreates=MipFragCreates,ipInDelivers,/,100,*';

$rrd_options .= " COMMENT:'% ipInDelivers   Current  Average  Maximum\\n'";

$rrd_options .= " LINE1.25:FragOKs#00cc00:'Frag OK      '";
$rrd_options .= ' GPRINT:FragOKs:LAST:%6.2lf%s';
$rrd_options .= ' GPRINT:FragOKs:AVERAGE:%6.2lf%s';
$rrd_options .= ' GPRINT:MFragOKs:MAX:%6.2lf%s\\\\n';

$rrd_options .= " LINE1.25:FragFails#cc0000:'Frag Fail    '";
$rrd_options .= ' GPRINT:FragFails:LAST:%6.2lf%s';
$rrd_options .= ' GPRINT:FragFails:AVERAGE:%6.2lf%s';
$rrd_options .= ' GPRINT:MFragFails:MAX:%6.2lf%s\\\\n';

$rrd_options .= " LINE1.25:ReasmOKs#006600:'Reasm OK     '";
$rrd_options .= ' GPRINT:ReasmOKs:LAST:%6.2lf%s';
$rrd_options .= ' GPRINT:ReasmOKs:AVERAGE:%6.2lf%s';
$rrd_options .= ' GPRINT:MReasmOKs:MAX:%6.2lf%s\\\\n';

$rrd_options .= " LINE1.25:ReasmFails#660000:'Reasm Fail   '";
$rrd_options .= ' GPRINT:ReasmFails:LAST:%6.2lf%s';
$rrd_options .= ' GPRINT:ReasmFails:AVERAGE:%6.2lf%s';
$rrd_options .= ' GPRINT:MReasmFails:MAX:%6.2lf%s\\\\n';

$rrd_options .= " LINE1.25:FragCreates#00cc:'Frag Create  '";
$rrd_options .= ' GPRINT:FragCreates:LAST:%6.2lf%s';
$rrd_options .= ' GPRINT:FragCreates:AVERAGE:%6.2lf%s';
$rrd_options .= ' GPRINT:MFragCreates:MAX:%6.2lf%s\\\\n';

$rrd_options .= " LINE1.25:ReasmReqds#000066:'Reasm Reqd   '";
$rrd_options .= ' GPRINT:ReasmReqds:LAST:%6.2lf%s';
$rrd_options .= ' GPRINT:ReasmReqds:AVERAGE:%6.2lf%s';
$rrd_options .= ' GPRINT:MReasmReqds:MAX:%6.2lf%s\\\\n';
