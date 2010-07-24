<?php

if($_GET['id']) { $interface = $_GET['id'];
} elseif($_GET['port']) { $interface = $_GET['port'];
} elseif($_GET['if']) { $interface = $_GET['if'];
} elseif($_GET['interface']) { $interface = $_GET['interface']; }

$query = mysql_query("SELECT * FROM `ports` AS I, `devices` AS D WHERE I.interface_id = '".$interface."'
                      AND I.device_id = D.device_id");
$port = mysql_fetch_array($query);

if(is_file($config['rrd_dir'] . "/" . $port['hostname'] . "/ifx-" . safename($port['ifIndex'] . ".rrd")))
{
  $rrd_filename = $config['rrd_dir'] . "/" . $port['hostname'] . "/ifx-" . safename($port['ifIndex'] . ".rrd");

  $rrd_list[1]['filename'] = $config['rrd_dir'] . "/" . $port['hostname'] . "/ifx-" . safename($port['ifIndex'] . ".rrd");
  $rrd_list[1]['descr'] = $int['ifDescr'];
  $rrd_list[1]['rra_in'] = "InBroadcastPkts";
  $rrd_list[1]['rra_out'] = "OutBroadcastPkts";
  $rrd_list[1]['descr']   = "Broadcast";
  $rrd_list[1]['colour_area_in'] = "BB77BB";
  $rrd_list[1]['colour_area_out'] = "FFDD88";

  $rrd_list[4]['filename'] = $config['rrd_dir'] . "/" . $port['hostname'] . "/ifx-" . safename($port['ifIndex'] . ".rrd");
  $rrd_list[4]['descr'] = $int['ifDescr'];
  $rrd_list[4]['rra_in'] = "InMulticastPkts";
  $rrd_list[4]['rra_out'] = "OutMulticastPkts";
  $rrd_list[4]['descr']   = "Multicast";
  $rrd_list[4]['colour_area_in'] = "805080";
  $rrd_list[4]['colour_area_out'] = "c0a060";

  $units='';
  $units_descr='Packets/sec';
  $total_units='B';
  $colours_in='greens';
  $multiplier = "1";
  $colours_out = 'blues';

  $nototal = 1;

  include ("includes/graphs/generic_multi_seperated.inc.php");

}
elseif(is_file($config['rrd_dir'] . "/" . $port['hostname'] . "/" . safename($port['ifIndex'] . ".rrd"))) 
{
  $rrd_filename = $config['rrd_dir'] . "/" . $port['hostname'] . "/" . safename($port['ifIndex'] . ".rrd");

  $rra_in = "INNUCASTPKTS";
  $rra_out = "OUTNUCASTPKTS";

  $colour_area_in = "AA66AA";
  $colour_line_in = "330033";
  $colour_area_out = "FFDD88";
  $colour_line_out = "FF6600";

  $colour_area_in_max = "cc88cc";
  $colour_area_out_max = "FFefaa";

  $unit_text = "Packets";

  $graph_max = 1;

  include("includes/graphs/generic_duplex.inc.php");

}

?>
