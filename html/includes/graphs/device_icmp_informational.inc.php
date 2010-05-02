<?php

include('common.inc.php');

$device = device_by_id_cache($id);

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/netstats-icmp.rrd";

$rrd_options .= " DEF:InSrcQuenchs=$rrd_filename:icmpInSrcQuenchs:AVERAGE";
$rrd_options .= " DEF:OutSrcQuenchs=$rrd_filename:icmpOutSrcQuenchs:AVERAGE";
$rrd_options .= " DEF:InRedirects=$rrd_filename:icmpInRedirects:AVERAGE";
$rrd_options .= " DEF:OutRedirects=$rrd_filename:icmpOutRedirects:AVERAGE";
$rrd_options .= " DEF:InAddrMasks=$rrd_filename:icmpInAddrMasks:AVERAGE";
$rrd_options .= " DEF:OutAddrMasks=$rrd_filename:icmpOutAddrMasks:AVERAGE";
$rrd_options .= " DEF:InAddrMaskReps=$rrd_filename:icmpInAddrMaskReps:AVERAGE";
$rrd_options .= " DEF:OutAddrMaskReps=$rrd_filename:icmpOutAddrMaskReps:AVERAGE";
$rrd_options .= " DEF:InSrcQuenchs_m=$rrd_filename:icmpInSrcQuenchs:MAX";
$rrd_options .= " DEF:OutSrcQuenchs_m=$rrd_filename:icmpOutSrcQuenchs:MAX";
$rrd_options .= " DEF:InRedirects_m=$rrd_filename:icmpInRedirects:MAX";
$rrd_options .= " DEF:OutRedirects_m=$rrd_filename:icmpOutRedirects:MAX";
$rrd_options .= " DEF:InAddrMasks_m=$rrd_filename:icmpInAddrMasks:MAX";
$rrd_options .= " DEF:OutAddrMasks_m=$rrd_filename:icmpOutAddrMasks:MAX";
$rrd_options .= " DEF:InAddrMaskReps_m=$rrd_filename:icmpInAddrMaskReps:MAX";
$rrd_options .= " DEF:OutAddrMaskReps_m=$rrd_filename:icmpOutAddrMaskReps:MAX";

$rrd_options .= " CDEF:OutSrcQuenchs_Inv=OutSrcQuenchs,-1,*";
$rrd_options .= " CDEF:OutRedirects_Inv=OutRedirects,-1,*";
$rrd_options .= " CDEF:OutAddrMasks_Inv=OutAddrMasks,-1,*";
$rrd_options .= " CDEF:OutAddrMaskReps_Inv=OutAddrMaskReps,-1,*";

$rrd_options .= " COMMENT:Packets/sec\ \ \ \ Current\ \ \ \ Average\ \ \ Maximum\\\\n";

$rrd_options .= " LINE1.25:InSrcQuenchs#00cc00:'InSrcQuenchs   '";
$rrd_options .= " GPRINT:InSrcQuenchs:LAST:%6.2lf%s";
$rrd_options .= " GPRINT:InSrcQuenchs:AVERAGE:%6.2lf%s";
$rrd_options .= " GPRINT:InSrcQuenchs_m:MAX:%6.2lf%s\\\\n";
$rrd_options .= " LINE1.25:OutSrcQuenchs_Inv#006600:'OutSrcQuenchs  '";
$rrd_options .= " GPRINT:OutSrcQuenchs:LAST:%6.2lf%s";
$rrd_options .= " GPRINT:OutSrcQuenchs:AVERAGE:%6.2lf%s";
$rrd_options .= " GPRINT:OutSrcQuenchs_m:MAX:%6.2lf%s\\\\n";

$rrd_options .= " LINE1.25:InRedirects#cc0000:'InRedirects    '";
$rrd_options .= " GPRINT:InRedirects:LAST:%6.2lf%s";
$rrd_options .= " GPRINT:InRedirects:AVERAGE:%6.2lf%s";
$rrd_options .= " GPRINT:InRedirects_m:MAX:'%6.2lf%s\\n'";
$rrd_options .= " LINE1.25:OutRedirects_Inv#660000:'OutRedirects   '";
$rrd_options .= " GPRINT:OutRedirects:LAST:%6.2lf%s";
$rrd_options .= " GPRINT:OutRedirects:AVERAGE:%6.2lf%s";
$rrd_options .= " GPRINT:OutRedirects_m:MAX:'%6.2lf%s\\n'";

$rrd_options .= " LINE1.25:InAddrMasks#0066cc:'InAddrMasks    '";
$rrd_options .= " GPRINT:InAddrMasks:LAST:'%6.2lf%s'";
$rrd_options .= " GPRINT:InAddrMasks:AVERAGE:'%6.2lf%s'";
$rrd_options .= " GPRINT:InAddrMasks:MAX:'%6.2lf%s\\n'";
$rrd_options .= " LINE1.25:OutAddrMasks_Inv#003399:'OutAddrMasks   '";
$rrd_options .= " GPRINT:OutAddrMasks:LAST:'%6.2lf%s'";
$rrd_options .= " GPRINT:OutAddrMasks:AVERAGE:'%6.2lf%s'";
$rrd_options .= " GPRINT:OutAddrMasks_m:MAX:'%6.2lf%s\\n'";

$rrd_options .= " LINE1.25:InAddrMaskReps#cc00cc:'InAddrMaskReps '";
$rrd_options .= " GPRINT:InAddrMaskReps:LAST:'%6.2lf%s'";
$rrd_options .= " GPRINT:InAddrMaskReps:AVERAGE:'%6.2lf%s'";
$rrd_options .= " GPRINT:InAddrMaskReps:MAX:'%6.2lf%s\\n'";
$rrd_options .= " LINE1.25:OutAddrMaskReps_Inv#990099:'OutAddrMaskReps'";
$rrd_options .= " GPRINT:OutAddrMaskReps:LAST:%6.2lf%s";
$rrd_options .= " GPRINT:OutAddrMaskReps:AVERAGE:%6.2lf%s";
$rrd_options .= " GPRINT:OutAddrMaskReps_m:MAX:%6.2lf%s\\\\n";

?>
