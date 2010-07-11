#!/usr/bin/php 
<?php

include("includes/defaults.inc.php");
include("config.php"); 
include("includes/functions.php");

if($argv[1]) { 
  $host      = strtolower($argv[1]);
  $community = $argv[2];
  $snmpver   = strtolower($argv[3]);
  if (is_numeric($argv[4]))
  	$port = $argv[4];
  else
  	$port = 161;

  if (!$snmpver) $snmpver = "v2c";
  if (!$community) $community = $config['community'];

  list($hostshort) 	= explode(".", $host);
  if ( mysql_result(mysql_query("SELECT COUNT(*) FROM `devices` WHERE `hostname` = '".mres($host)."'"), 0) == '0' ) {
    if ( isDomainResolves($argv[1])){
      if ( isPingable($argv[1])) {
       if ( isSNMPable($argv[1], $community, $snmpver, $port)) {
        $snmphost = trim(str_replace("\"", "", shell_exec($config['snmpget'] ." -m SNMPv2-MIB -Oqv -$snmpver -c $community $host:$port sysName.0")));
        if ($snmphost != "" || $snmphost && ($snmphost == $host || $hostshort = $host)) {
          $return = createHost ($host, $community, $snmpver, $port);
	  if($return) { echo($return . "\n"); } else { echo("Adding $host failed\n"); }
        } else { echo("Given hostname does not match SNMP-read hostname!\n"); }
       } else { echo("Could not reach $host with SNMP\n"); }
      } else { echo("Could not ping $host\n"); }
    } else { echo("Could not resolve $host\n"); }
  } else { echo("Already got host $host\n"); }
} else { echo("Add Host Tool\nUsage: ./addhost.php <hostname> [community] [v1|v2c] [port]\n"); }

?>
