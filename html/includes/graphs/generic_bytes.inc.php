<?php

/// Draw generic octets graph
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
$rrd_options .= " CDEF:doutoctets_max=outoctets_max,-1,*";

$rrd_options .= " VDEF:totin=inoctets,TOTAL";
$rrd_options .= " VDEF:totout=outoctets,TOTAL";
$rrd_options .= " VDEF:tot=octets,TOTAL";

#$rrd_options .= " VDEF:95thin=inoctets,95,PERCENT";
#$rrd_options .= " VDEF:95thout=outoctets,95,PERCENT";
#$rrd_options .= " VDEF:d95thout=doutoctets,5,PERCENT";

$rrd_options .= " AREA:inoctets_max#aDEB7B:";
$rrd_options .= " AREA:inoctets#CDEB8B:";
$rrd_options .= " COMMENT:'Bytes/sec           Now       Ave      Max\\n'";
$rrd_options .= " LINE1.25:inoctets#006600:'In            '";
$rrd_options .= " GPRINT:inoctets:LAST:%6.2lf%s";
$rrd_options .= " GPRINT:inoctets:AVERAGE:%6.2lf%s";
$rrd_options .= " GPRINT:inoctets_max:MAX:%6.2lf%s\\\\n";
#$rrd_options .= " GPRINT:95thin:%6.2lf%s\\\\n";
$rrd_options .= " AREA:doutoctets_max#a3b9FF:";
$rrd_options .= " AREA:doutoctets#C3D9FF:";
$rrd_options .= " LINE1.25:doutoctets#000099:'Out           '";
$rrd_options .= " GPRINT:outoctets:LAST:%6.2lf%s";
$rrd_options .= " GPRINT:outoctets:AVERAGE:%6.2lf%s";
$rrd_options .= " GPRINT:outoctets_max:MAX:%6.2lf%s\\\\n";
#$rrd_options .= " GPRINT:95thout:%6.2lf%s\\\\n";
$rrd_options .= " GPRINT:tot:'Total %6.2lf%s'";
$rrd_options .= " GPRINT:totin:'(In %6.2lf%s'";
$rrd_options .= " GPRINT:totout:'Out %6.2lf%s)\\\\l'";
#$rrd_options .= " LINE1:95thin#aa0000";
#$rrd_options .= " LINE1:d95thout#aa0000";

?>
