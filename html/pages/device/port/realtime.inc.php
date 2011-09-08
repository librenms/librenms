<?php

### FIXME - do this in a function and/or do it in graph-realtime.php


if($_GET['optc']) {
  $interval = $_GET['optc'];
} else {
  if($device['os'] == "linux") {
    $interval = "15";
  } else {
    $interval = "2";
  }
}

print_optionbar_start();

echo("Polling Interval: ");

if ($interval == "0.25" || !$interval) { echo("<span class='pagemenu-selected'>"); }
echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/port/".$interface['interface_id']."/realtime/0.25/'>0.25s</a>");
if ($interval == "0.25" || !$interval) { echo("</span>"); }

echo(" | ");

if ($interval == "1" || !$interval) { echo("<span class='pagemenu-selected'>"); }
echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/port/".$interface['interface_id']."/realtime/1/'>1s</a>");
if ($interval == "1" || !$interval) { echo("</span>"); }


echo(" | ");

if ($interval == "2" || !$interval) { echo("<span class='pagemenu-selected'>"); }
echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/port/".$interface['interface_id']."/realtime/2/'>2s</a>");
if ($interval == "2" || !$interval) { echo("</span>"); }

echo(" | ");

if ($interval == "5" || !$interval) { echo("<span class='pagemenu-selected'>"); }
echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/port/".$interface['interface_id']."/realtime/5/'>5s</a>");
if ($interval == "5" || !$interval) { echo("</span>"); }

echo(" | ");

if ($interval == "15" || !$interval) { echo("<span class='pagemenu-selected'>"); }
echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/port/".$interface['interface_id']."/realtime/15/'>15s</a>");
if ($interval == "15" || !$interval) { echo("</span>"); }

echo(" | ");

if ($interval == "60" || !$interval) { echo("<span class='pagemenu-selected'>"); }
echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/port/".$interface['interface_id']."/realtime/60/'>60s</a>");
if ($interval == "60" || !$interval) { echo("</span>"); }

print_optionbar_end();

?>

<div align="center" style="margin: 30px;">
<object data="graph-realtime.php?type=bits&id=<?php echo($interface['interface_id'] . "&interval=".$interval); ?>" type="image/svg+xml" width="1000" height="400">
<param name="src" value="graph.php?type=bits&id=<?php echo($interface['interface_id'] . "&interval=".$interval); ?>" />
Your browser does not support the type SVG! You need to either use Firefox or download the Adobe SVG plugin.
</object>
</div>
