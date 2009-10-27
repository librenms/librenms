<?php

/// Draws aggregate bits graph from multiple RRDs
/// Variables : colour_[line|area]_[in|out], rrd_filenames

include("common.inc.php");

$i=0;
foreach($rrd_filenames as $rrd_filename) {
    $rrd_options .= " DEF:inoctets" . $i . "=".$rrd_filename.":".$rra_in.":AVERAGE";
    $rrd_options .= " DEF:outoctets" . $i . "=".$rrd_filename.":".$rra_out.":AVERAGE";
    $in_thing .= $seperator . "inoctets" . $i . ",UN,0," . "inoctets" . $i . ",IF";
    $out_thing .= $seperator . "outoctets" . $i . ",UN,0," . "outoctets" . $i . ",IF";
    $pluses .= $plus;
    $seperator = ",";
    $plus = ",+";
    $i++;
}

if($inverse) { $in = 'out'; $out = 'in'; } else { $in = 'in'; $out = 'out'; }
  $rrd_options .= " CDEF:".$in."octets=" . $in_thing . $pluses;
  $rrd_options .= " CDEF:".$out."octets=" . $out_thing . $pluses;
  $rrd_options .= " CDEF:doutoctets=outoctets,-1,*";
  $rrd_options .= " CDEF:inbits=inoctets,8,*";
  $rrd_options .= " CDEF:outbits=outoctets,8,*";
  $rrd_options .= " CDEF:doutbits=doutoctets,8,*";
  if($legend == 'no' || $legend == '1') {
   $rrd_options .= " AREA:inbits#".$colour_area_in.":";
   $rrd_options .= " LINE1.25:inbits#".$colour_line_in.":";
   $rrd_options .= " AREA:doutbits#".$colour_area_out.":";
   $rrd_options .= " LINE1.25:doutbits#".$colour_line_out.":";
  } else {
   $rrd_options .= " AREA:inbits#".$colour_area_in.":";
   $rrd_options .= " COMMENT:BPS\ \ \ \ Current\ \ \ Average\ \ \ \ \ \ Max\\\\n";
   $rrd_options .= " LINE1.25:inbits#".$colour_line_in.":In\ ";
   $rrd_options .= " GPRINT:inbits:LAST:%6.2lf%s";
   $rrd_options .= " GPRINT:inbits:AVERAGE:%6.2lf%s";
   $rrd_options .= " GPRINT:inbits:MAX:%6.2lf%s\\\\l";
   $rrd_options .= " AREA:doutbits#".$colour_area_out.":";
   $rrd_options .= " LINE1.25:doutbits#".$colour_line_out.":Out";
   $rrd_options .= " GPRINT:outbits:LAST:%6.2lf%s";
   $rrd_options .= " GPRINT:outbits:AVERAGE:%6.2lf%s";
   $rrd_options .= " GPRINT:outbits:MAX:%6.2lf%s\\\l";
  }

?>
