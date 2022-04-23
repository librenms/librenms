<?php

require 'includes/html/graphs/common.inc.php';

$rrd_filename = Rrd::name($device['hostname'], 'ipSystemStats-ipv4');

$rrd_options .= " DEF:ipInDelivers=$rrd_filename:InDelivers:AVERAGE";
$rrd_options .= " DEF:ipReasmReqds=$rrd_filename:ReasmReqds:AVERAGE";
$rrd_options .= " DEF:ipReasmOKs=$rrd_filename:ReasmOKs:AVERAGE";
$rrd_options .= " DEF:ipReasmFails=$rrd_filename:ReasmFails:AVERAGE";
$rrd_options .= " DEF:ipFragFails=$rrd_filename:OutFragFails:AVERAGE";
$rrd_options .= " DEF:ipFragCreates=$rrd_filename:OutFragCreates:AVERAGE";

$rrd_options .= " DEF:MipInDelivers=$rrd_filename:InDelivers:MAX";
$rrd_options .= " DEF:MipReasmOKs=$rrd_filename:ReasmOKs:MAX";
$rrd_options .= " DEF:MipReasmReqds=$rrd_filename:ReasmReqds:MAX";
$rrd_options .= " DEF:MipReasmFails=$rrd_filename:ReasmFails:MAX";
$rrd_options .= " DEF:MipFragFails=$rrd_filename:OutFragFails:MAX";
$rrd_options .= " DEF:MipFragCreates=$rrd_filename:OutFragCreates:MAX";

$rrd_options .= ' CDEF:ReasmReqds=ipReasmReqds,ipInDelivers,/,100,*';
$rrd_options .= ' CDEF:ReasmOKs=ipReasmOKs,ipInDelivers,/,100,*';
$rrd_options .= ' CDEF:ReasmFails=ipReasmFails,ipInDelivers,/,100,*';
$rrd_options .= ' CDEF:FragFails=ipFragFails,ipInDelivers,/,100,*';
$rrd_options .= ' CDEF:FragCreates=ipFragCreates,ipInDelivers,/,100,*';

$rrd_options .= ' CDEF:FragFails_n=FragFails,-1,*';
$rrd_options .= ' CDEF:FragCreates_n=FragCreates,-1,*';

$rrd_options .= ' CDEF:MReasmReqds=MipReasmReqds,MipInDelivers,/,100,*';
$rrd_options .= ' CDEF:MReasmOKs=MipReasmOKs,MipInDelivers,/,100,*';
$rrd_options .= ' CDEF:MReasmFails=MipReasmFails,MipInDelivers,/,100,*';
$rrd_options .= ' CDEF:MFragFails=MipFragFails,MipInDelivers,/,100,*';
$rrd_options .= ' CDEF:MFragCreates=MipFragCreates,MipInDelivers,/,100,*';

$rrd_options .= " COMMENT:'% ipInDelivers   Current  Average  Maximum\\n'";

$rrd_options .= " LINE1.25:FragFails_n#cc0000:'Frag Fail    '";
$rrd_options .= ' GPRINT:FragFails:LAST:%6.2lf%s';
$rrd_options .= ' GPRINT:FragFails:AVERAGE:%6.2lf%s';
$rrd_options .= ' GPRINT:MFragFails:MAX:%6.2lf%s\\\\n';

$rrd_options .= " LINE1.25:FragCreates#00cc:'Frag Create  '";
$rrd_options .= ' GPRINT:FragCreates:LAST:%6.2lf%s';
$rrd_options .= ' GPRINT:FragCreates:AVERAGE:%6.2lf%s';
$rrd_options .= ' GPRINT:MFragCreates:MAX:%6.2lf%s\\\\n';

$rrd_options .= " LINE1.25:ReasmOKs#006600:'Reasm OK     '";
$rrd_options .= ' GPRINT:ReasmOKs:LAST:%6.2lf%s';
$rrd_options .= ' GPRINT:ReasmOKs:AVERAGE:%6.2lf%s';
$rrd_options .= ' GPRINT:MReasmOKs:MAX:%6.2lf%s\\\\n';

$rrd_options .= " LINE1.25:ReasmFails#660000:'Reasm Fail   '";
$rrd_options .= ' GPRINT:ReasmFails:LAST:%6.2lf%s';
$rrd_options .= ' GPRINT:ReasmFails:AVERAGE:%6.2lf%s';
$rrd_options .= ' GPRINT:MReasmFails:MAX:%6.2lf%s\\\\n';

$rrd_options .= " LINE1.25:ReasmReqds#000066:'Reasm Reqd   '";
$rrd_options .= ' GPRINT:ReasmReqds:LAST:%6.2lf%s';
$rrd_options .= ' GPRINT:ReasmReqds:AVERAGE:%6.2lf%s';
$rrd_options .= ' GPRINT:MReasmReqds:MAX:%6.2lf%s\\\\n';
