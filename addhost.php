#!/usr/bin/php 
<?

include("config.php"); 
include("includes/functions.php");

if($argv[1] && $argv[2] && $argv[3]) { 
  $host      = strtolower($argv[1]);
  $community = $argv[2];
  $snmpver   = strtolower($argv[3]);
  if (is_numeric($argv[4]))
  	$port = $argv[4];
  else
  	$port = 161;

  list($hostshort) 	= explode(".", $host);
  if ( isDomainResolves($argv[1])){
    if ( isPingable($argv[1])) { 
      if ( mysql_result(mysql_query("SELECT COUNT(*) FROM `devices` WHERE `hostname` = '".mres($host)."'"), 0) == '0' ) {
        $snmphost = trim(`snmpget -Oqv -$snmpver -c $community $host:$port sysName.0 | sed s/\"//g`);
#	var_dump($snmphost);
        if ($snmphost == $host || $hostshort = $host) {
          $return = createHost ($host, $community, $snmpver, $port);
#	  var_dump($return);
	  if($return) { echo($return . "\n"); } else { echo("Adding $host failed\n"); }
        } else { echo("Given hostname does not match SNMP-read hostname!\n"); }
      } else { echo("Already got host $host\n"); }
    } else { echo("Could not ping $host\n"); }
  } else { echo("Could not resolve $host\n"); }
} else { echo("Add Host Tool\nUsage: ./addhost.php <hostname> <community> <snmpversion> <port>\n"); }

?>
