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

$rra_in = "INERRORS";
$rra_out = "OUTERRORS";

$colour_area_in = "FF3300";
$colour_line_in = "FF0000";
$colour_area_out = "FF6633";
$colour_line_out = "CC3300";

$colour_area_in_max = "FF6633";
$colour_area_out_max = "FF9966";

$graph_max = 1;

$unit_text = "Errors";

include("generic_duplex.inc.php");

?>
