<?php

function graph_mac_acc_total ($interface, $graph, $from, $to, $width, $height, $title, $vertical) {
  global $config, $installdir;
  list($interface, $type) = explode("-", $interface);
  $imgfile = $config['install_dir'] . "/graphs/" . "$graph";
  $options = "--alt-autoscale-max -E --start $from --end " . ($to - 150) . " --width $width --height $height ";
  $options .= $config['rrdgraph_def_text'];
  if($height < "99") { $options .= " --only-graph "; }
  $hostname = gethostbyid($device);
  $sql = "SELECT *, (bps_in + bps_out) AS bps FROM `mac_accounting` AS M, `interfaces` AS I, `devices` AS D WHERE M.interface_id = '$interface'
          AND I.interface_id = M.interface_id AND I.device_id = D.device_id ORDER BY bps DESC LIMIT 0,10";
  $query = mysql_query($sql);
  if($width <= "300") { $options .= "--font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  $pluses = ""; $iter = '0';
  $options .= " COMMENT:'                                             In                    Out\\\\n'";
  while($acc = mysql_fetch_array($query)) {
   if($type == "pkts") {
     $this_rrd = $config['rrd_dir'] . "/" . $acc['hostname'] . "/mac-accounting/" . $acc['ifIndex'] . "-" . $acc['mac'] . "-pkts.rrd";
     $units='pps';
   } else {
     $this_rrd = $config['rrd_dir'] . "/" . $acc['hostname'] . "/mac-accounting/" . $acc['ifIndex'] . "-" . $acc['mac'] . ".rrd";
     $units='bps';
   }
   if(is_file($this_rrd)) {

   $name = $acc['mac'];
   $addy = mysql_fetch_array(mysql_query("SELECT * FROM ipv4_mac where mac_address = '".$acc['mac']."'"));
   if($addy) {
     $name = @gethostbyaddr($addy['ipv4_address']);
   }

    $this_id = str_replace(".", "", $acc['mac']);
    if(!$config['graph_colours'][$iter]) { $iter = 0; }
    $colour=$config['graph_colours'][$iter];
    $descr = str_pad($name, 36);
    $descr = substr($descr,0,36);
    $options .= " DEF:in".$this_id."=$this_rrd:IN:AVERAGE ";
    $options .= " DEF:out".$this_id."temp=$this_rrd:OUT:AVERAGE ";
    $options .= " CDEF:inB".$this_id."=in".$this_id.",8,* ";
    $options .= " CDEF:outB".$this_id."temp=out".$this_id."temp,8,*";
    $options .= " CDEF:outB".$this_id."=outB".$this_id."temp,-1,*";
    $options .= " CDEF:octets".$this_id."=inB".$this_id.",outB".$this_id."temp,+";
    $options .= " VDEF:totin".$this_id."=inB".$this_id.",TOTAL";
    $options .= " VDEF:totout".$this_id."=outB".$this_id."temp,TOTAL";
    $options .= " VDEF:tot".$this_id."=octets".$this_id.",TOTAL";
    $options .= " AREA:inB".$this_id."#" . $colour . ":'" . $descr . "':STACK";
    if($optionsb) {$stack="STACK";}
    $optionsb .= " AREA:outB".$this_id."#" . $colour . "::$stack";
    $options .= " GPRINT:inB".$this_id.":LAST:%6.2lf%s$units";
    $options .= " GPRINT:totin".$this_id.":\(%6.2lf%sB\)";
    $options .= " GPRINT:outB".$this_id."temp:LAST:%6.2lf%s$units";
    $options .= " GPRINT:totout".$this_id.":\(%6.2lf%sB\)\\\\n";
    $iter++;
   }
  }
  $options .= $optionsb;
  $thing = shell_exec($config['rrdtool'] . " graph $imgfile $options");
  return $imgfile;
}

$port = $_GET['if'];
$graph = graph_mac_acc_total ($port, $graphfile, $from, $to, $width, $height, $title, $vertical);

?>
