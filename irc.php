#!/usr/bin/env php

# port info is broken.  always reports same values no matter host selected

<?php

# Disable annoying messages... well... all messages actually :)
error_reporting(0);

include_once("includes/defaults.inc.php");
include_once("config.php");
include_once("includes/functions.php");
include_once("includes/discovery/functions.inc.php");

include_once('Net/SmartIRC.php');

mysql_close();

# Redirect to /dev/null or logfile if you aren't using screen to keep tabs
echo "Observer Bot Starting ...\n";
echo "\n";
echo "Timestamp         Command\n";
echo "----------------- ------- \n";

class observiumbot
{

###
# HELP Function
###
  function help_info(&$irc, &$data)
  {
    global $config;

    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "Commands: .help, .log, .status, .version, .down, .port, .device, .listdevices");

    echo date("m-d-y H:i:s ");
    echo "HELP\n";

    mysql_close();
  }

###
# VERSION Function
###
  function version_info(&$irc, &$data)
  {
    global $config;

    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "Observium Version " . $config['version']);

    echo date("m-d-y H:i:s ");
    echo "VERSION\t\t". $config['version'] . "\n";

    mysql_close();
  }

###
# LOG Function
###
  function log_info(&$irc, &$data)
  {
    global $config;

    mysql_connect($config['db_host'],$config['db_user'],$config['db_pass']);
    mysql_select_db($config['db_name']);

    $device = dbFetchRow("SELECT `event_id`,`host`,`datetime`,`message`,`type` FROM `eventlog` ORDER BY `event_id` DESC LIMIT 1");
    $host = $device['host'];
    $hostid = dbFetchRow("SELECT `hostname` FROM `devices` WHERE `device_id` = $host");

    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $device['event_id'] ." ". $hostid['hostname'] ." ". $device['datetime'] ." ". $device['message'] ." ". $device['type']);

    echo date("m-d-y H:i:s ");
    echo "LOG\n";

    mysql_close();
  }

###
# DOWN Function
###
  function down_info(&$irc, &$data)
  {
    global $config;

    mysql_connect($config['db_host'],$config['db_user'],$config['db_pass']);
    mysql_select_db($config['db_name']);

    foreach (dbFetchRows("SELECT * FROM `devices` where status=0") as $device)
    {
      $message .= $sep . $device['hostname'];
      $sep = ", ";
    }
    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $message);

    mysql_close();

    echo date("m-d-y H:i:s ");
    echo "DOWN\n";
  }

###
# DEVICE Function
###
  function device_info(&$irc, &$data)
  {
    global $config;

    $hostname = $data->messageex[1];

    mysql_connect($config['db_host'],$config['db_user'],$config['db_pass']);
    mysql_select_db($config['db_name']);

    $device = dbFetchRow("SELECT * FROM `devices` WHERE `hostname` = ?",array($hostname));

    mysql_close();

if (!$device) {
 $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "Error: Bad or Missing hostname, use .listdevices to show all devices."); } else {

    if ($device['status'] == 1) { $status = "Up " . formatUptime($device['uptime'] . " "); } else { $status = "Down "; }
    if ($device['ignore']) { $status = "*Ignored*"; }
    if ($device['disabled']) { $status = "*Disabled*"; }

    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $device['os'] . " " . $device['version'] . " " .
      $device['features'] . " " . $status);

    echo date("m-d-y H:i:s ");
    echo "DEVICE\t\t". $device['hostname']."\n";
  }
}
###
# PORT Function
###
  function port_info(&$irc, &$data)
  {
    global $config;

    $hostname = $data->messageex[1];
    $ifname = $data->messageex[2];

    mysql_connect($config['db_host'],$config['db_user'],$config['db_pass']);
    mysql_select_db($config['db_name']);

    $device = dbFetchRow("SELECT * FROM `devices` WHERE `hostname` = ?",array($device));
    $port   = dbFetchRow("SELECT * FROM `ports` WHERE `ifName` = ? OR `ifDescr` = ? AND device_id = ?", array($ifname, $ifname, $device['device_id']));

    mysql_close();

    $bps_in = formatRates($port['ifInOctets_rate']);
    $bps_out = formatRates($port['ifOutOctets_rate']);
    $pps_in = format_bi($port['ifInUcastPkts_rate']);
    $pps_out = format_bi($port['ifOutUcastPkts_rate']);

    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, $port['ifAdminStatus'] . "/" . $port['ifOperStatus'] . " " .
      $bps_in. " > bps > " . $bps_out . " | " . $pps_in. "pps > PPS > " . $pps_out ."pps");

    echo date("m-d-y H:i:s ");
    echo "PORT\t\t\t" . $hostname . "\t". $ifname . "\n";
  }

###
# LISTDEVICES Function
###
  function list_devices(&$irc, &$data)
  {
    global $config;

    unset ($message);

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

    echo date("m-d-y H:i:s ");
    echo "LISTDEVICES\n";
  }

###
# STATUS Function
###
  function status_info(&$irc, &$data)
  {
    global $config;

    $statustype = $data->messageex[1];

    mysql_connect($config['db_host'],$config['db_user'],$config['db_pass']);
    mysql_select_db($config['db_name']);

    if ($statustype == "dev")
    {
      $devcount = array_pop(dbFetchRow("SELECT count(*) FROM devices"));
      $devup = array_pop(dbFetchRow("SELECT count(*) FROM devices  WHERE status = '1' AND `ignore` = '0'"));
      $devdown = array_pop(dbFetchRow("SELECT count(*) FROM devices WHERE status = '0' AND `ignore` = '0'"));
      $devign = array_pop(dbFetchRow("SELECT count(*) FROM devices WHERE `ignore` = '1'"));
      $devdis = array_pop(dbFetchRow("SELECT count(*) FROM devices WHERE `disabled` = '1'"));
      $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "Devices: " .$devcount . " (" .$devup . " up, " .$devdown . " down, " .$devign . " ignored, " .$devdis . " disabled" . ")");
    } else if ($statustype == "prt") {
      $prtcount = array_pop(dbFetchRow("SELECT count(*) FROM ports"));
      $prtup = array_pop(dbFetchRow("SELECT count(*) FROM ports AS I, devices AS D  WHERE I.ifOperStatus = 'up' AND I.ignore = '0' AND I.device_id = D.device_id AND D.ignore = '0'"));
      $prtdown = array_pop(dbFetchRow("SELECT count(*) FROM ports AS I, devices AS D WHERE I.ifOperStatus = 'down' AND I.ifAdminStatus = 'up' AND I.ignore = '0' AND D.device_id = I.device_id AND D.ignore = '0'"));
      $prtsht = array_pop(dbFetchRow("SELECT count(*) FROM ports AS I, devices AS D WHERE I.ifAdminStatus = 'down' AND I.ignore = '0' AND D.device_id = I.device_id AND D.ignore = '0'"));
      $prtign = array_pop(dbFetchRow("SELECT count(*) FROM ports AS I, devices AS D WHERE D.device_id = I.device_id AND (I.ignore = '1' OR D.ignore = '1')"));
      $prterr = array_pop(dbFetchRow("SELECT count(*) FROM ports AS I, devices AS D WHERE D.device_id = I.device_id AND (I.ignore = '0' OR D.ignore = '0') AND (I.ifInErrors_delta > '0' OR I.ifOutErrors_delta > '0')"));
      $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "Ports: " .$prtcount . " (" .$prtup . " up, " .$prtdown . " down, " .$prtign . " ignored, " .$prtsht . " shutdown" . ")");
    } else if ($statustype == "srv") {
      $srvcount = array_pop(dbFetchRow("SELECT count(service_id) FROM services"));
      $srvup = array_pop(dbFetchRow("SELECT count(service_id) FROM services  WHERE service_status = '1' AND service_ignore ='0'"));
      $srvdown = array_pop(dbFetchRow("SELECT count(service_id) FROM services WHERE service_status = '0' AND service_ignore = '0'"));
      $srvign = array_pop(dbFetchRow("SELECT count(service_id) FROM services WHERE service_ignore = '1'"));
      $srvdis = array_pop(dbFetchRow("SELECT count(service_id) FROM services WHERE service_disabled = '1'"));
      $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "Services: " .$srvcount . " (" .$srvup . " up, " .$srvdown . " down, " .$srvign . " ignored, " .$srvdis . " disabled" . ")");
    } else {
        $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, "Error: STATUS requires one of the following <dev prt srv>"); }

    mysql_close();

    echo date("m-d-y H:i:s ");
    echo "STATUS\t\t$statustype\n";
  }
}

$bot = &new observiumbot();
$irc = &new Net_SmartIRC();
$irc->setUseSockets(TRUE);

$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '.listdevices', $bot, 'list_devices');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '.device', $bot, 'device_info');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '.port', $bot, 'port_info');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '.down', $bot, 'down_info');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '.version', $bot, 'version_info');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '.status', $bot, 'status_info');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '.log', $bot, 'log_info');
$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '.help', $bot, 'help_info');

$irc->connect($config['irc_host'], $config['irc_port']);
$irc->login($config['irc_nick'], 'Observium Bot', 0, $config['irc_nick']);
$irc->join($config['irc_chan']);
$irc->listen();
$irc->disconnect();

?>
