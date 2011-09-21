<?php

/// Draw generic bits graph
/// args: ds_in, ds_out, rrd_filename, bg, legend, from, to, width, height, inverse

include("includes/graphs/common.inc.php");

if ($rrd_filename) { $rrd_filename_out = $rrd_filename; $rrd_filename_in = $rrd_filename; }

if ($inverse) { $in = 'out'; $out = 'in'; } else { $in = 'in'; $out = 'out'; }

if ($multiplier)
{
  $rrd_options .= " DEF:p".$out."octets=".$rrd_filename_out.":".$ds_out.":AVERAGE";
  $rrd_options .= " DEF:p".$in."octets=".$rrd_filename_in.":".$ds_in.":AVERAGE";
  $rrd_options .= " DEF:p".$out."octets_max=".$rrd_filename_out.":".$ds_out.":MAX";
  $rrd_options .= " DEF:p".$in."octets_max=".$rrd_filename_in.":".$ds_in.":MAX";
  $rrd_options .= " CDEF:inoctets=pinoctets,$multiplier,*";
  $rrd_options .= " CDEF:outoctets=poutoctets,$multiplier,*";
  $rrd_options .= " CDEF:inoctets_max=pinoctets_max,$multiplier,*";
  $rrd_options .= " CDEF:outoctets_max=poutoctets_max,$multiplier,*";
} else {
  $rrd_options .= " DEF:".$out."octets=".$rrd_filename_out.":".$ds_out.":AVERAGE";
  $rrd_options .= " DEF:".$in."octets=".$rrd_filename_in.":".$ds_in.":AVERAGE";
  $rrd_options .= " DEF:".$out."octets_max=".$rrd_filename_out.":".$ds_out.":MAX";
  $rrd_options .= " DEF:".$in."octets_max=".$rrd_filename_in.":".$ds_in.":MAX";
}

$rrd_options .= " CDEF:octets=inoctets,outoctets,+";
$rrd_options .= " CDEF:doutoctets=outoctets,-1,*";
$rrd_options .= " CDEF:outbits=outoctets,8,*";
$rrd_options .= " CDEF:outbits_max=outoctets_max,8,*";
$rrd_options .= " CDEF:doutoctets_max=outoctets_max,-1,*";
$rrd_options .= " CDEF:doutbits=doutoctets,8,*";
$rrd_options .= " CDEF:doutbits_max=doutoctets_max,8,*";

$rrd_options .= " CDEF:inbits=inoctets,8,*";
$rrd_options .= " CDEF:inbits_max=inoctets_max,8,*";

if ($config['rrdgraph_real_95th']) {
        $rrd_options .= " CDEF:highbits=inoctets,outoctets,MAX,8,*";
        $rrd_options .= " VDEF:95thhigh=highbits,95,PERCENT";
}

$rrd_options .= " VDEF:totin=inoctets,TOTAL";
$rrd_options .= " VDEF:totout=outoctets,TOTAL";
$rrd_options .= " VDEF:tot=octets,TOTAL";

$rrd_options .= " VDEF:95thin=inbits,95,PERCENT";
$rrd_options .= " VDEF:95thout=outbits,95,PERCENT";
$rrd_options .= " VDEF:d95thout=doutbits,5,PERCENT";

$rrd_options .= " AREA:inbits_max#aDEB7B:";
$rrd_options .= " AREA:inbits#CDEB8B:";
$rrd_options .= " COMMENT:'bps      Now       Ave      Max      95th %\\n'";
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

if ($config['rrdgraph_real_95th']) {
        $rrd_options .= " HRULE:95thhigh#FF0000:\"Highest\"";
        $rrd_options .= " GPRINT:95thhigh:\"%30.2lf%s\\n\"";
}

$rrd_options .= " GPRINT:tot:'Total %6.2lf%s'";
$rrd_options .= " GPRINT:totin:'(In %6.2lf%s'";
$rrd_options .= " GPRINT:totout:'Out %6.2lf%s)\\\\l'";
$rrd_options .= " LINE1:95thin#aa0000";
$rrd_options .= " LINE1:d95thout#aa0000";

?>
