<?php

/// Draw generic bits graph
/// args: rra_in, rra_out, rrd_filename, bg, legend, from, to, width, height, inverse

include("includes/graphs/common.inc.php");

if($inverse) { $in = 'out'; $out = 'in'; } else { $in = 'in'; $out = 'out'; }

$rrd_options .= " DEF:".$out."octets=".$rrd_filename.":".$rra_out.":AVERAGE";
$rrd_options .= " DEF:".$in."octets=".$rrd_filename.":".$rra_in.":AVERAGE";
$rrd_options .= " DEF:".$out."octets_max=".$rrd_filename.":".$rra_out.":MAX";
$rrd_options .= " DEF:".$in."octets_max=".$rrd_filename.":".$rra_in.":MAX";

$rrd_options .= " CDEF:octets=inoctets,outoctets,+";
$rrd_options .= " CDEF:doutoctets=outoctets,-1,*";
$rrd_options .= " CDEF:inbits=inoctets,8,*";
$rrd_options .= " CDEF:inbits_max=inoctets_max,8,*";
$rrd_options .= " CDEF:outbits_max=outoctets_max,8,*";
$rrd_options .= " CDEF:doutoctets_max=outoctets_max,-1,*";
$rrd_options .= " CDEF:doutbits_max=doutoctets_max,8,*";
$rrd_options .= " CDEF:outbits=outoctets,8,*";
$rrd_options .= " CDEF:doutbits=doutoctets,8,*";
$rrd_options .= " VDEF:totin=inoctets,TOTAL";
$rrd_options .= " VDEF:totout=outoctets,TOTAL";
$rrd_options .= " VDEF:tot=octets,TOTAL";
$rrd_options .= " VDEF:95thin=inbits,95,PERCENT";
$rrd_options .= " VDEF:95thout=outbits,95,PERCENT";
$rrd_options .= " VDEF:d95thout=doutbits,5,PERCENT";

$rrd_options .= " AREA:inbits_max#aDEB7B:";
$rrd_options .= " AREA:inbits#CDEB8B:";
$rrd_options .= " COMMENT:'BPS      Now       Ave      Max      95th %\\n'";
$rrd_options .= " LINE1.25:inbits#006600:'In '";
$rrd_options .= " GPRINT:inbits:LAST:%6.2lf%s";
$rrd_options .= " GPRINT:inbits:AVERAGE:%6.2lf%s";
$rrd_options .= " GPRINT:inbits_max:MAX:%6.2lf%s";
$rrd_options .= " GPRINT:95thin:%6.2lf%s\\\\n";
$rrd_options .= " AREA:doutbits_max#a3b9FF:";
$rrd_options .= " AREA:doutbits#C3D9FF:";
$rrd_options .= " LINE1.25:doutbits#000099:Out";
$rrd_options .= " GPRINT:outbits:LAST:%6.2lf%s";
$rrd_options .= " GPRINT:outbits:AVERAGE:%6.2lf%s";
$rrd_options .= " GPRINT:outbits_max:MAX:%6.2lf%s";
$rrd_options .= " GPRINT:95thout:%6.2lf%s\\\\n";
$rrd_options .= " GPRINT:tot:'Total %6.2lf%s'";
$rrd_options .= " GPRINT:totin:'(In %6.2lf%s'";
$rrd_options .= " GPRINT:totout:'Out %6.2lf%s)\\\\l'";
$rrd_options .= " LINE1:95thin#aa0000";
$rrd_options .= " LINE1:d95thout#aa0000";

?>
