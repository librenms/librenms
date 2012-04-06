<?php

if (file_exists($config['rrd_dir'] . "/" . $device['hostname'] . "/port-". $port['ifIndex'] . "-adsl.rrd"))
{
  $iid = $id;
  echo("<div class=graphhead>ADSL Line Speed</div>");
  $graph_type = "port_adsl_speed";

  include("includes/print-interface-graphs.inc.php");

  echo("<div class=graphhead>ADSL Line Attenuation</div>");
  $graph_type = "port_adsl_attenuation";

  include("includes/print-interface-graphs.inc.php");

  echo("<div class=graphhead>ADSL Line SNR Margin</div>");
  $graph_type = "port_adsl_snr";

  include("includes/print-interface-graphs.inc.php");

  echo("<div class=graphhead>ADSL Output Powers</div>");
  $graph_type = "port_adsl_power";

  include("includes/print-interface-graphs.inc.php");
}

?>
