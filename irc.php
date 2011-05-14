#!/usr/bin/env php

# status <dev prt srv>
# reboot
# log
# help
# down

<?php

# Disable annoying messages... well... all messages actually :)
error_reporting(0);

include_once("includes/defaults.inc.php");
include_once("config.php");
include_once("includes/functions.php");
include_once("includes/discovery/functions.inc.php");

include_once('Net/SmartIRC.php');

mysql_close();

# Redirect to /dev/null if you aren't using screen to keep tabs
echo "Observer Bot Starting ...\n";
echo "\n";
echo "COMMAND\t\tHOST\t\t\tDEVICE\n";
echo "-------\t\t----\t\t\t------\n";

class observiumbot

{

###
# Get status on !version 
###
  function version_info(&$irc, &$data)
  {

global $config;

$irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "Observium Version " . $config['version']);

echo "VERSION\t\t". $config['version'] . "\n";

mysql_close();

  }

###
# Get status on !down devices
###
  function down_info(&$irc, &$data)
  {

global $config;
mysql_connect($config['db_host'],$config['db_user'],$config['db_pass']);
mysql_select_db($config['db_name']);

    $query = mysql_query("SELECT * FROM `devices` where status=0");
    unset($message);
    while($device = mysql_fetch_assoc($query))
    {
      $message .= $sep . $device['hostname'];
      $sep = ", ";
    }
    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $message);
    unset($sep);

mysql_close();

echo "DOWN\n";

  }

###
# Get status on !device <hostname>
###
  function device_info(&$irc, &$data)
  {

    $hostname = $data->messageex[1];

global $config;
mysql_connect($config['db_host'],$config['db_user'],$config['db_pass']);
mysql_select_db($config['db_name']);

    $device = dbFetchRow("SELECT * FROM `devices` WHERE `hostname` = ?",array($hostname));

mysql_close();

    if ($device['status'] == 1) { $status = "Up " . formatUptime($device['uptime'] . " "); } else { $status = "Down "; }
    if ($device['ignore']) { $status = "*Ignored*"; }
    if ($device['disabled']) { $status = "*Disabled*"; }

    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, '#'.$device['device_id'] . " " . $device['os'] . " " . $device['version'] . " " .
      $device['features'] . " " . $status);

echo "DEVICE\t\t". $device['hostname']."\n";

  }


###
# Get status on !port <hostname port>
###
  function port_info(&$irc, &$data)
  {
    $hostname = $data->messageex[1];
    $ifname = $data->messageex[2];

global $config;
mysql_connect($config['db_host'],$config['db_user'],$config['db_pass']);
mysql_select_db($config['db_name']);

    $device = dbFetchRow("SELECT * FROM `devices` WHERE `hostname` = ?",array($device));
    $port   = dbFetchRow("SELECT * FROM `ports` WHERE `ifName` = ? OR `ifDescr` = ? AND device_id = ?", array($ifname, $ifname, $device['device_id']));

mysql_close();

    $bps_in = formatRates($port['ifInOctets_rate']);
    $bps_out = formatRates($port['ifOutOctets_rate']);
    $pps_in = format_bi($port['ifInUcastPkts_rate']);
    $pps_out = format_bi($port['ifOutUcastPkts_rate']);

    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, '#' . $port['interface_id'] . " " . $port['ifAdminStatus'] . "/" . $port['ifOperStatus'] . " " .
      $bps_in. " > bps > " . $bps_out . " | " . $pps_in. "pps > PPS > " . $pps_out ."pps");

echo "PORT\t\t" . $hostname . "\t". $ifname . "\n";

  }


###
# !listdevices lists all devices
###
  function list_devices(&$irc, &$data)
  {
    unset ($message);

global $config;
mysql_connect($config['db_host'],$config['db_user'],$config['db_pass']);
mysql_select_db($config['db_name']);

    foreach (dbFetchRows("SELECT `hostname` FROM `devices`") as $device)
    {
      $message .= $sep . $device['hostname'];
      $sep = ", ";
    }

mysql_close();

    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $message);
    unset($sep);

echo "LISTDEVICES\n";

  }
}

$bot = &new observiumbot();
$irc = &new Net_SmartIRC();
$irc->setUseSockets(TRUE);

$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!listdevices', $bot, 'list_devices');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!device', $bot, 'device_info');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!port', $bot, 'port_info');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!down', $bot, 'down_info');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '!version', $bot, 'version_info');

$irc->connect($config['irc_host'], $config['irc_port']);
$irc->login($config['irc_nick'], 'Observium Bot', 0, $config['irc_nick']);
$irc->join($config['irc_chan']);
$irc->listen();
$irc->disconnect();

?>
