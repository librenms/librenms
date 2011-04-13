<?php

if (!isset($_GET['optb']) ) { $_GET['optb'] = "graphs"; }

$interface_query = mysql_query("select * from ports WHERE interface_id = '".$_GET['opta']."'");
$interface = mysql_fetch_assoc($interface_query);

$port_details = 1;

$hostname = $device['hostname'];
$hostid   = $device['interface_id'];
$ifname   = $interface['ifDescr'];
$ifIndex   = $interface['ifIndex'];
$speed = humanspeed($interface['ifSpeed']);

$ifalias = $interface['name'];

if ($interface['ifPhysAddress']) { $mac = "$interface[ifPhysAddress]"; }

$color = "black";
if ($interface['ifAdminStatus'] == "down") { $status = "<span class='grey'>Disabled</span>"; }
if ($interface['ifAdminStatus'] == "up" && $interface['ifOperStatus'] == "down") { $status = "<span class='red'>Enabled / Disconnected</span>"; }
if ($interface['ifAdminStatus'] == "up" && $interface['ifOperStatus'] == "up") { $status = "<span class='green'>Enabled / Connected</span>"; }

$i = 1;
$inf = fixifName($ifname);

$bg="#ffffff";

$show_all = 1;

echo("<div class=ifcell style='margin: 0px;'><table width=100% cellpadding=10 cellspacing=0>");

include("includes/print-interface.inc.php");

echo("</table></div>");

$pos = strpos(strtolower($ifname), "vlan");
if ($pos !== false )
{
  $broke = yes;
}

$pos = strpos(strtolower($ifname), "loopback");

if ($pos !== false )
{
  $broke = yes;
}

echo("<div style='clear: both;'>");

print_optionbar_start();

if ($_GET['optb'] == "graphs" || !$_GET['optb']) { echo("<span class='pagemenu-selected'>"); }
echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/port/".$interface['interface_id']."/'>Graphs</a>");
if ($_GET['optb'] == "graphs" || !$_GET['optb']) { echo("</span>"); }


if (mysql_result(mysql_query("SELECT COUNT(*) FROM `ports_adsl` WHERE `interface_id` = '".$interface['interface_id']."'"),0) )
{
  echo(" | ");
  if ($_GET['optb'] == "adsl") { echo("<span class='pagemenu-selected'>"); }
  echo("<a href='".$config['base_url']."/device/".$device['device_id']."/port/".$interface['interface_id']."/adsl/'>ADSL</a>");
  if ($_GET['optb'] == "adsl") { echo("</span>"); }
}

echo(" | ");

if ($_GET['optb'] == "arp") { echo("<span class='pagemenu-selected'>"); }
echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/port/".$interface['interface_id']."/arp/'>ARP Table</a>");
if ($_GET['optb'] == "arp") { echo("</span>"); }

if (mysql_result(mysql_query("SELECT COUNT(*) FROM `ports` WHERE `pagpGroupIfIndex` = '".
                              $interface['ifIndex']."' and `device_id` = '".$device['device_id']."'"),0) )
{
  echo(" | ");
  if ($_GET['optb'] == "pagp") { echo("<span class='pagemenu-selected'>"); }
  echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/port/".$interface['interface_id']."/pagp/'>PAgP</a>");
  if ($_GET['optb'] == "pagp") { echo("</span>"); }
}


if (mysql_result(mysql_query("SELECT count(*) FROM mac_accounting WHERE interface_id = '".$interface['interface_id']."'"),0) > "0" )
{
  echo(" | Mac Accounting : ");
  if ($_GET['optb'] == "macaccounting" && $_GET['optc'] == "bits" && !$_GET['optd']) { echo("<span class='pagemenu-selected'>"); }
  echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/port/".$interface['interface_id']."/macaccounting/bits/'>Bits</a>");
  if ($_GET['optb'] == "macaccounting" && $_GET['optc'] == "bits" && !$_GET['optd']) { echo("</span>"); }
  echo("(");
  if ($_GET['optb'] == "macaccounting" && $_GET['optc'] == "bits" && $_GET['optd'] == "thumbs") { echo("<span class='pagemenu-selected'>"); }
  echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/port/".$interface['interface_id']."/macaccounting/bits/thumbs/'>Mini</a>");
  if ($_GET['optb'] == "macaccounting" && $_GET['optc'] == "bits" && $_GET['optd'] == "thumbs") { echo("</span>"); }
  echo('|');
  if ($_GET['optb'] == "macaccounting" && $_GET['optc'] == "bits" && $_GET['optd'] == "top10") { echo("<span class='pagemenu-selected'>"); }
  echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/port/".$interface['interface_id']."/macaccounting/bits/top10/'>Top10</a>");
  if ($_GET['optb'] == "macaccounting" && $_GET['optc'] == "bits" && $_GET['optd'] == "top10") { echo("</span>"); }
  echo(") | ");
  if ($_GET['optb'] == "macaccounting" && $_GET['optc'] == "pkts" && !$_GET['optd']) { echo("<span class='pagemenu-selected'>"); }
  echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/port/".$interface['interface_id']."/macaccounting/pkts/'>Packets</a>");
  if ($_GET['optb'] == "macaccounting" && $_GET['optc'] == "pkts" && !$_GET['optd']) { echo("</span>"); }
  echo("(");
  if ($_GET['optb'] == "macaccounting" && $_GET['optc'] == "pkts" && $_GET['optd'] == "thumbs") { echo("<span class='pagemenu-selected'>"); }
  echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/port/".$interface['interface_id']."/macaccounting/pkts/thumbs/'>Mini</a>");
  if ($_GET['optb'] == "macaccounting" && $_GET['optc'] == "pkts" && $_GET['optd'] == "thumbs") { echo("</span>"); }
  echo(")");
}

if (mysql_result(mysql_query("SELECT COUNT(*) FROM juniAtmVp WHERE interface_id = '".$interface['interface_id']."'"),0) > "0" )
{
  echo(" | ATM VPs : ");
  if ($_GET['optb'] == "junose-atm-vp" && $_GET['optc'] == "bits") { echo("<span class='pagemenu-selected'>"); }
  echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/port/".$interface['interface_id']."/junose-atm-vp/bits/'>Bits</a>");
  if ($_GET['optb'] == "junose-atm-vp" && $_GET['optc'] == "bits") { echo("</span>"); }
  echo(" | ");
  if ($_GET['optb'] == "junose-atm-vp" && $_GET['optc'] == "packets") { echo("<span class='pagemenu-selected'>"); }
  echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/port/".$interface['interface_id']."/junose-atm-vp/packets/'>Packets</a>");
  if ($_GET['optb'] == "junose-atm-vp" && $_GET['optc'] == "bits") { echo("</span>"); }
  echo(" | ");
  if ($_GET['optb'] == "junose-atm-vp" && $_GET['optc'] == "cells") { echo("<span class='pagemenu-selected'>"); }
  echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/port/".$interface['interface_id']."/junose-atm-vp/cells/'>Cells</a>");
  if ($_GET['optb'] == "junose-atm-vp" && $_GET['optc'] == "bits") { echo("</span>"); }
  echo(" | ");
  if ($_GET['optb'] == "junose-atm-vp" && $_GET['optc'] == "errors") { echo("<span class='pagemenu-selected'>"); }
  echo("<a href='".$config['base_url']."/device/" . $device['device_id'] . "/port/".$interface['interface_id']."/junose-atm-vp/errors/'>Errors</a>");
  if ($_GET['optb'] == "junose-atm-vp" && $_GET['optc'] == "bits") { echo("</span>"); }
}

print_optionbar_end();

echo("<div style='margin: 5px;'>");
include("pages/device/port/".mres($_GET['optb']).".inc.php");
echo("</div>");

?>
