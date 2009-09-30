<?php

function graph_multi_bits ($args) {

  include("common.inc.php");

  $i = 1;
  foreach(explode(",", $args['interfaces']) as $ifid) {
    $query = mysql_query("SELECT `ifIndex`, `hostname` FROM `interfaces` AS I, devices as D WHERE I.interface_id = '" . $ifid . "' AND I.device_id = D.device_id");
    $int = mysql_fetch_row($query);
    if(is_file($config['rrd_dir'] . "/" . $int[1] . "/" . $int[0] . ".rrd")) {
      $options .= " DEF:inoctets" . $i . "=" . $config['rrd_dir'] . "/" . $int[1] . "/" . $int[0] . ".rrd:INOCTETS:AVERAGE";
      $options .= " DEF:outoctets" . $i . "=" . $config['rrd_dir'] . "/" . $int[1] . "/" . $int[0] . ".rrd:OUTOCTETS:AVERAGE";
      $in_thing .= $seperator . "inoctets" . $i . ",UN,0," . "inoctets" . $i . ",IF";
      $out_thing .= $seperator . "outoctets" . $i . ",UN,0," . "outoctets" . $i . ",IF";
      $pluses .= $plus;
      $seperator = ",";
      $plus = ",+";
      $i++;
    }
  }
  if($inverse) { $in = 'out'; $out = 'in'; } else { $in = 'in'; $out = 'out'; }
  $options .= " CDEF:".$in."octets=" . $in_thing . $pluses;
  $options .= " CDEF:".$out."octets=" . $out_thing . $pluses;
  $options .= " CDEF:doutoctets=outoctets,-1,*";
  $options .= " CDEF:inbits=inoctets,8,*";
  $options .= " CDEF:outbits=outoctets,8,*";
  $options .= " CDEF:doutbits=doutoctets,8,*";
  if($args['legend'] == '0') {
   $options .= " AREA:inbits#CDEB8B:";
   $options .= " LINE1.25:inbits#006600:";
   $options .= " AREA:doutbits#C3D9FF:";
   $options .= " LINE1.25:doutbits#000099:";
  } else {
   $options .= " AREA:inbits#CDEB8B:";
   $options .= " COMMENT:BPS\ \ \ \ Current\ \ \ Average\ \ \ \ \ \ Max\\\\n";
   $options .= " LINE1.25:inbits#006600:In\ ";
   $options .= " GPRINT:inbits:LAST:%6.2lf%s";
   $options .= " GPRINT:inbits:AVERAGE:%6.2lf%s";
   $options .= " GPRINT:inbits:MAX:%6.2lf%s\\\\l";
   $options .= " AREA:doutbits#C3D9FF:";
   $options .= " LINE1.25:doutbits#000099:Out";
   $options .= " GPRINT:outbits:LAST:%6.2lf%s";
   $options .= " GPRINT:outbits:AVERAGE:%6.2lf%s";
   $options .= " GPRINT:outbits:MAX:%6.2lf%s\\\l";
  }
  $thing = shell_exec($config['rrdtool'] . " graph $graphfile $options");
  return $graphfile;
}

if($_GET['if']) { $interfaces = $_GET['if']; }
if($_GET['interfaces']) { $interfaces = $_GET['interfaces']; }

$args['interfaces'] = $interfaces;
$args['graphfile'] = $graphfile;
$args['from']      = $from;
$args['to']        = $to;
$args['width']     = $width;
$args['height']    = $height;

$graph = graph_multi_bits ($args);

?>
