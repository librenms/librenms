#!/usr/bin/php
<?php

include("includes/defaults.inc.php");
include("config.php");
include("includes/functions.php");
include("includes/discovery/functions.inc.php");

include_once('Net/SmartIRC.php');


class observiumbot
{

  function device_info (&$irc, &$data)
  {
     $hostname = $data->messageex[1];

     $device = mysql_fetch_array(mysql_query("SELECT * FROM `devices` WHERE `hostname` = '".mres($hostname)."'"));

     if($device['status'] == 1) { $status = "Up " . formatUptime($device['uptime'] . " "); } else { $status = "Down "; }
     if($device['ignore']) { $status = "*Ignored*"; }
     if($device['disabled']) { $status = "*Disabled*"; }

     $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'id'.$device['device_id'] . " " . $device['os'] . " " . $device['version'] . " " . 
                   $device['features'] . " " . $status);

  }
 
  function port_info (&$irc, &$data)
  {

    $hostname = $data->messageex[1];
    $ifname = $data->messageex[2];

    $device = mysql_fetch_array(mysql_query("SELECT * FROM `devices` WHERE `hostname` = '".mres($hostname)."'"));
    $port   = mysql_fetch_Array(mysql_query("SELECT * FROM `ports` WHERE `ifName` = '".$ifname."' OR `ifDescr` = '".$ifname."' AND device_id = '".$device['device_id']."'"));

     $bps_in = formatRates($port['ifInOctets_rate']);
     $bps_out = formatRates($port['ifOutOctets_rate']);
     $pps_in = format_bi($port['ifInUcastPkts_rate']);
     $pps_out = format_bi($port['ifOutUcastPkts_rate']);

    $irc->message(SMARTIRC_TYPE_CHANNEL, $data->channel, 'id' . $port['interface_id'] . " " . $port['ifAdminStatus'] . "/" . $port['ifOperStatus'] . " " . 
                  $bps_in. " > bps > " . $bps_out . " | " . $pps_in. "pps > PPS > " . $pps_out ."pps");

  }
}

$host = "chat.eu.freenode.net";
$port = 6667;
$nick = "ObserverBOT";
$chan = "#observium";

$bot = &new observiumbot( );
$irc = &new Net_SmartIRC( );
$irc->setUseSockets( TRUE );

$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '!device', $bot, 'device_info' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '!port', $bot, 'port_info' );

$irc->connect( $host, $port );
$irc->login( $nick, 'Observium Bot', 0, $nick );
$irc->join( array( $chan ) );
$irc->listen( );
$irc->disconnect( );

?>
