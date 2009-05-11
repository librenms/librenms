#!/usr/bin/php
<?

#ini_set('display_errors', 1);
#ini_set('display_startup_errors', 1);
#ini_set('log_errors', 1);
#ini_set('error_reporting', E_WARNING);

include("config.php");
include("includes/functions.php");
include("includes/cdp.inc.php");

if($argv[1] == "--device" && $argv[2]) {
  $where = "AND `device_id` = '".$argv[2]."'";
}


$device_query = mysql_query("SELECT * FROM `devices` WHERE `status` = '1' AND (`os` = 'IOS' OR `os` = 'IOS XE') AND hostname NOT LIKE '%esr%' $where ORDER BY `device_id` DESC");

while ($device = mysql_fetch_array($device_query)) {

  $hostname = $device['hostname'];
  $community = $device['community'];
  $id = $device['device_id'];
  $host = $id;

  echo("\nDetecting CDP neighbours on $device[1]...\n");
  $snmp = new snmpCDP($hostname, $community);
  $ports = $snmp->getports();
  $cdp = $snmp->explore_cdp($ports);

  $cdp_links = "";

  foreach (array_keys($cdp) as $key) {
    $port = $ports[$key];
    $link = $cdp[$key];
    $cdp_links .= $hostname . "," . $port['desc'] . "," . $link['host'] . "," . $link['port'] . "\n";   
  }
        
  $cdp_links = trim($cdp_links);

  foreach ( explode("\n" ,$cdp_links) as $link ) {
    if ($link == "") { break; }
    list($src_host,$src_if, $dst_host, $dst_if) = explode(",", $link);
    $dst_host = strtolower($dst_host);  
    $dst_if = strtolower($dst_if);
    $src_host = strtolower($src_host);
    $src_if = strtolower($src_if);
#    if ( isDomainResolves($dst_host . "." . $config['mydomain']) ) { 
#      $dst_host = $dst_host . "." . $config['mydomain']; 
#    }
    $ip = gethostbyname($dst_host);
    if ( match_network($config['nets'], $ip) ) {	   
      if ( mysql_result(mysql_query("SELECT COUNT(*) FROM `devices` WHERE `sysName` = '$dst_host'"), 0) == '0' ) {
        if($config['cdp_autocreate']) {
          echo("++ Creating: $dst_host \n");
          createHost ($dst_host, $community, "v2c");
        }
      } else { 
        echo("."); 
      }
    } else { 
      echo("!($dst_host)"); 
    }

    if ( mysql_result(mysql_query("SELECT COUNT(*) FROM `devices` WHERE `sysName` = '$dst_host'"), 0) == '1' && 
      mysql_result(mysql_query("SELECT COUNT(*) FROM `devices` WHERE `hostname` = '$src_host'"), 0) == '1' &&
      mysql_result(mysql_query("SELECT COUNT(*) FROM `interfaces` AS I, `devices` AS D WHERE `ifDescr` = '$dst_if' AND sysName = '$dst_host' AND D.device_id = I.device_id"), 0) == '1' && 
      mysql_result(mysql_query("SELECT COUNT(*) FROM `interfaces` AS I, `devices` AS D WHERE `ifDescr` = '$src_if' AND hostname = '$src_host' AND D.device_id = I.device_id"), 0) == '1')
    {
      $dst_if_id   = mysql_result(mysql_query("SELECT I.interface_id FROM `interfaces` AS I, `devices` AS D WHERE `ifDescr` = '$dst_if' AND sysName = '$dst_host' AND D.device_id = I.device_id"), 0);
      $src_if_id   = mysql_result(mysql_query("SELECT I.interface_id FROM `interfaces` AS I, `devices` AS D WHERE `ifDescr` = '$src_if' AND hostname = '$src_host' AND D.device_id = I.device_id"), 0);
      $linkalive[] = $src_if_id . "," . $dst_if_id;
      if ( mysql_result(mysql_query("SELECT COUNT(*) FROM `links` WHERE `dst_if` = '$dst_if_id' AND `src_if` = '$src_if_id'"),0) == '0') 
      { 
        $sql = "INSERT INTO `links` (`src_if`, `dst_if`, `cdp`) VALUES ('$src_if_id', '$dst_if_id', '1')";
        mysql_query($sql);
        echo("\n++($src_host $src_if -> $dst_host $dst_if)");
      } else { 
        echo(".."); 
      }
    } else {

    } 
  }
}

echo("\n");

echo(count($linkalive) . " Entries\n");

$query = mysql_query("SELECT * FROM `links`");
while($entry = mysql_fetch_array($query)) {
  $i = 0;
  unset($alive);
  while ($i < count($linkalive) && !$alive) {
    list($src_if_id,$dst_if_id) = explode(",", $linkalive[$i]);
    $thislink = $entry['src_if'] . "," .  $entry['dst_if'];
    if ($thislink == $linkalive[$i]) {
      $alive = "yes";
    }
    $i++;
  }
  if (!$alive) { 
    mysql_query("DELETE FROM `links` WHERE `src_if` = '$entry[src_if]' AND `dst_if` = '$entry[dst_if]'"); 
#    echo("$src_if_id -> $dst_if_id REMOVED \n");
  } else {
#    echo("$src_if_id -> $dst_if_id VALID \n");
  }
}

?>
