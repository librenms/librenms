<?php

if($_GET['id']) { $interface = $_GET['id'];
} elseif($_GET['port']) { $interface = $_GET['port'];
} elseif($_GET['if']) { $interface = $_GET['if'];
} elseif($_GET['interface']) { $interface = $_GET['interface']; }

$query = mysql_query("SELECT * FROM `interfaces` AS I, `devices` AS D WHERE I.interface_id = '".$interface."'
                      AND I.device_id = D.device_id");

$port = mysql_fetch_array($query);
if(is_file($config['rrd_dir'] . "/" . $port['hostname'] . "/" . safename($port['ifIndex'] . ".rrd"))) {
  $rrd_filename = $config['rrd_dir'] . "/" . $port['hostname'] . "/" . safename($port['ifIndex'] . ".rrd");
}

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

include("generic_duplex.inc.php");

?>
