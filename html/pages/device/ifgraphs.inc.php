<?php
echo("
<div style='float: right; text-align: right;'>
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/ifgraphs/bits/'>Bits</a> | 
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/ifgraphs/pkts/'>Packets</a> | 
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/ifgraphs/nupkts/'>NU Packets</a> |
<a href='".$config['base_url']."/device/" . $device['device_id'] . "/ifgraphs/errors/'>Errors</a>
</div>");

$dographs = 1;

if($_GET['opta']) { $graph_type = $_GET['opta']; } else  { $graph_type = "bits"; }

include("pages/device/ifs.inc.php");

?>
