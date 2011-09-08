<?php

print_optionbar_start();

echo("Polling Interval: ");

if ($_GET['optc'] == "0.25" || !$_GET['optc']) { echo("<span class='pagemenu-selected'>"); }
echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/port/".$interface['interface_id']."/realtime/0.25/'>0.25s</a>");
if ($_GET['optc'] == "0.25" || !$_GET['optc']) { echo("</span>"); }

echo(" | ");

if ($_GET['optc'] == "1" || !$_GET['optc']) { echo("<span class='pagemenu-selected'>"); }
echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/port/".$interface['interface_id']."/realtime/1/'>1s</a>");
if ($_GET['optc'] == "1" || !$_GET['optc']) { echo("</span>"); }


echo(" | ");

if ($_GET['optc'] == "2" || !$_GET['optc']) { echo("<span class='pagemenu-selected'>"); }
echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/port/".$interface['interface_id']."/realtime/2/'>2s</a>");
if ($_GET['optc'] == "2" || !$_GET['optc']) { echo("</span>"); }

echo(" | ");

if ($_GET['optc'] == "5" || !$_GET['optc']) { echo("<span class='pagemenu-selected'>"); }
echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/port/".$interface['interface_id']."/realtime/5/'>5s</a>");
if ($_GET['optc'] == "5" || !$_GET['optc']) { echo("</span>"); }

echo(" | ");

if ($_GET['optc'] == "15" || !$_GET['optc']) { echo("<span class='pagemenu-selected'>"); }
echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/port/".$interface['interface_id']."/realtime/15/'>15s</a>");
if ($_GET['optc'] == "15" || !$_GET['optc']) { echo("</span>"); }

echo(" | ");

if ($_GET['optc'] == "60" || !$_GET['optc']) { echo("<span class='pagemenu-selected'>"); }
echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/port/".$interface['interface_id']."/realtime/60/'>60s</a>");
if ($_GET['optc'] == "60" || !$_GET['optc']) { echo("</span>"); }

print_optionbar_end();


### FIXME - do this in a function and/or do it in graph-realtime.php

if($device['os'] == "linux") { 
  $interval = "15"; 
} else {
  $interval = "2";
}

if($_GET['optc']) { $interval = $_GET['optc']; }

?>

<div align="center" style="margin: 30px;">
<object data="graph-realtime.php?type=bits&id=<?php echo($interface['interface_id'] . "&interval=".$interval); ?>" type="image/svg+xml" width="1000" height="400">
<param name="src" value="graph.php?type=bits&id=<?php echo($interface['interface_id'] . "&interval=".$interval); ?>" />
Your browser does not support the type SVG! You need to either use Firefox or download the Adobe SVG plugin.
</object>
</div>
