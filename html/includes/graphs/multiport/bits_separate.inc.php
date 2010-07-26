<?php

function graph_multi_bits ($args) {

  include("includes/graphs/common.inc.php");
  $i = 1;
  $options .= " COMMENT:'                               In\: Current     Maximum      '";
  if(!$args['nototal']) {$options .= " COMMENT:'Total      '";}
  $options .= " COMMENT:'Out\: Current     Maximum'";
  if(!$args['nototal']) {$options .= " COMMENT:'     Total'";}
  $options .= " COMMENT:'\\\\n'";

  foreach(explode(",", $args['ports']) as $ifid) {
    $query = mysql_query("SELECT * FROM `ports` AS I, devices as D WHERE I.interface_id = '" . $ifid . "' AND I.device_id = D.device_id");
    $int = mysql_fetch_array($query);
    $this_rrd = $config['rrd_dir'] . "/" . $int['hostname'] . "/" . safename($int['ifIndex'] . ".rrd");
    $units='bps'; $unit='B'; $colours='greens'; $multiplier = "8"; $coloursb = 'blues';
    if(is_file($this_rrd)) {
      $name = $int['ifDescr'];
      if(!$config['graph_colours'][$colours][$iter] || !$config['graph_colours'][$coloursb][$iter]) { $iter = 0; }
      $colour=$config['graph_colours'][$colours][$iter];
      $colourb=$config['graph_colours'][$coloursb][$iter];
      $descr = str_pad($name, 30);
      $descr = substr($descr,0,30);
      $options .= " DEF:in".$ifid."=$this_rrd:INOCTETS:AVERAGE ";
      $options .= " DEF:out".$ifid."temp=$this_rrd:OUTOCTETS:AVERAGE ";
      $options .= " CDEF:inB".$ifid."=in".$ifid.",$multiplier,* ";
      $options .= " CDEF:outB".$ifid."temp=out".$ifid."temp,$multiplier,*";
      $options .= " CDEF:outB".$ifid."=outB".$ifid."temp,-1,*";
      $options .= " CDEF:octets".$ifid."=inB".$ifid.",outB".$ifid."temp,+";
      if(!$args['nototal']) {
        $options .= " VDEF:totin".$ifid."=inB".$ifid.",TOTAL";
        $options .= " VDEF:totout".$ifid."=outB".$ifid."temp,TOTAL";
        $options .= " VDEF:tot".$ifid."=octets".$ifid.",TOTAL";
      }
      $options .= " HRULE:999999999999999#" . $colourb . ":\\\s:";
      $options .= " AREA:inB".$ifid."#" . $colour . ":'" . $descr . "':STACK";
      if($optionsb) {$stack="STACK";}
      $optionsb .= " AREA:outB".$ifid."#" . $colourb . "::$stack";
      $options .= " GPRINT:inB".$ifid.":LAST:%6.2lf%s$units";
      $options .= " GPRINT:inB".$ifid.":MAX:%6.2lf%s$units";
      if(!$args['nototal']) {
        $options .= " GPRINT:totin".$ifid.":%6.2lf%s$unit";
      }
      $options .= " COMMENT:'    '";
      $options .= " GPRINT:outB".$ifid."temp:LAST:%6.2lf%s$units";
      $options .= " GPRINT:outB".$ifid."temp:MAX:%6.2lf%s$units";
      if(!$args['nototal']) {
        $options .= " GPRINT:totout".$ifid.":%6.2lf%s$unit";
      }
       $options .= " COMMENT:\\\\n";
      $iter++;
    }
  }
  $options .= $optionsb;
  $options .= " HRULE:0#999999";
  #echo($config['rrdtool'] . " graph $graphfile $options");
  $thing = shell_exec($config['rrdtool'] . " graph $graphfile $options");
  return $graphfile;
}

if($_GET['if']) { $args['ports'] = $_GET['if']; }
if($_GET['ports']) { $args['ports'] = $_GET['ports']; }
if($ports) {$args['ports'] = $ports; }

$args['graphfile'] = $graphfile;
$args['from']      = $from;
$args['to']        = $to;
$args['width']     = $width;
$args['height']    = $height;
if($_GET['legend']) {
  $args['legend']    = $_GET['legend'];
}
$graph = graph_multi_bits ($args);

?>
