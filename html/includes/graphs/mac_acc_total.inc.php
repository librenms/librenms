<?php

function graph_mac_acc_total ($args) {
  global $config;
  if($args['height'] < "99") { $options .= " --only-graph "; }
  if($args['sort'] == "in" || $args['sort'] == "out") { $sort = "bps_" . $args['sort']; } else { $sort = "bps"; }
  $imgfile = $config['install_dir'] . "/graphs/" . $args['graphfile'];
  $options .= " --alt-autoscale-max -E --start ".$args['from']." --end " . ($args['to'] - 150) . " --width ".$args['width']." --height ".$args['height']." ";
  $options .= $config['rrdgraph_def_text'];
  $sql = "SELECT *, (bps_in + bps_out) AS bps FROM `mac_accounting` AS M, `interfaces` AS I, `devices` AS D WHERE M.interface_id = '".$args['port']."'
          AND I.interface_id = M.interface_id AND I.device_id = D.device_id ORDER BY $sort DESC LIMIT 0,10";
  $query = mysql_query($sql);
  if($args['width'] <= "300") { $options .= "--font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $pluses = ""; $iter = '0';
  $options .= " COMMENT:'                                     In\: Current     Maximum      Total      Out\: Current     Maximum     Total\\\\n'";
  while($acc = mysql_fetch_array($query)) {
   if($args['stat'] == "pkts") {
     $this_rrd = $config['rrd_dir'] . "/" . $acc['hostname'] . "/mac-accounting/" . $acc['ifIndex'] . "-" . $acc['mac'] . "-pkts.rrd";
     $units='pps'; $unit = 'p'; $multiplier = '1';
     $colours = 'purples';
   } elseif ($args['stat'] == "bits") {
     $this_rrd = $config['rrd_dir'] . "/" . $acc['hostname'] . "/mac-accounting/" . $acc['ifIndex'] . "-" . $acc['mac'] . ".rrd";
     $units='bps'; $unit='B'; $multiplier='8';
     $colours='greens';
   }
   if(is_file($this_rrd)) {
   $name = $acc['mac'];
   $addy = mysql_fetch_array(mysql_query("SELECT * FROM ipv4_mac where mac_address = '".$acc['mac']."'"));
   if($addy) {
     $name = @gethostbyaddr($addy['ipv4_address']);
   } 
    $this_id = str_replace(".", "", $acc['mac']);
    if(!$config['graph_colours'][$colours][$iter]) { $iter = 0; }
    $colour=$config['graph_colours'][$colours][$iter];
    $descr = str_pad($name, 36);
    $descr = substr($descr,0,36);
    $options .= " DEF:in".$this_id."=$this_rrd:IN:AVERAGE ";
    $options .= " DEF:out".$this_id."temp=$this_rrd:OUT:AVERAGE ";
    $options .= " CDEF:inB".$this_id."=in".$this_id.",$multiplier,* ";
    $options .= " CDEF:outB".$this_id."temp=out".$this_id."temp,$multiplier,*";
    $options .= " CDEF:outB".$this_id."=outB".$this_id."temp,-1,*";
    $options .= " CDEF:octets".$this_id."=inB".$this_id.",outB".$this_id."temp,+";
    $options .= " VDEF:totin".$this_id."=inB".$this_id.",TOTAL";
    $options .= " VDEF:totout".$this_id."=outB".$this_id."temp,TOTAL";
    $options .= " VDEF:tot".$this_id."=octets".$this_id.",TOTAL";
    $options .= " AREA:inB".$this_id."#" . $colour . ":'" . $descr . "':STACK";
    if($optionsb) {$stack="STACK";}
    $optionsb .= " AREA:outB".$this_id."#" . $colour . "::$stack";
    $options .= " GPRINT:inB".$this_id.":LAST:%6.2lf%s$units";
    $options .= " GPRINT:inB".$this_id.":MAX:%6.2lf%s$units";
    $options .= " GPRINT:totin".$this_id.":%6.2lf%s$unit";
    $options .= " COMMENT:'    '";
    $options .= " GPRINT:outB".$this_id."temp:LAST:%6.2lf%s$units";
    $options .= " GPRINT:outB".$this_id."temp:MAX:%6.2lf%s$units";
    $options .= " GPRINT:totout".$this_id.":%6.2lf%s$unit\\\\n";
    $iter++;
   }
  }
  $options .= $optionsb;
  $thing = shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}

$args['port']      = $_GET['port'];
$args['stat']      = $_GET['stat'];
$args['sort']      = $_GET['sort'];
$args['graphfile'] = $graphfile;
$args['from']      = $from;
$args['to']        = $to;
$args['width']     = $width;
$args['height']    = $height;

$graph = graph_mac_acc_total ($args);

?>
