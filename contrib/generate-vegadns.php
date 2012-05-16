#!/usr/bin/env php
<?php

include("config.php");
include("includes/functions.php");


$dnsdblink = mysql_connect('localhost', 'user', 'pass');
$dnsdb = mysql_select_db('tinydns', $dnsdblink);

$link = mysql_connect($config['db_host'], $config['db_user'], $config['db_pass']);
$db = mysql_select_db($config['db_name'], $link);


$query = "SELECT * FROM ipaddr AS A, ports as I, devices as D WHERE A.port_id = I.port_id AND I.device_id = D.device_id AND D.hostname LIKE '%.vostron.net' AND D.hostname NOT LIKE '%.cust.%' AND D.os = 'ios'";
$data = mysql_query($query, $link);
while($ip = mysql_fetch_array($data)) {
  unset($sub);
  $hostname = $ip['hostname'];

  $real_hostname = $hostname;

  $hostname = str_replace(".vostron.net", "", $hostname);
  list($loc, $host) = explode("-", $hostname);
  if($host) {
    $hostname = "$host.$loc.v4.vostron.net";
  } else {
    $host = $loc; unset ($loc);
    $hostname = "$host.v4.vostron.net";
  }

  $interface = $ip['ifDescr'];
  $address = $ip['addr'];
  $cidr = $ip['cidr'];
  $interface = strtolower(makeshortif(fixifname($interface)));
  $interface = str_replace("/", "-", $interface);
  list($interface, $sub) = explode(".", $interface);
  if($sub) {
     $sub = str_replace(" ", "", $sub);
     $sub = str_replace("aal5", "", $sub);
     $interface = "$sub.$interface";
  }
  $hostip = trim(gethostbyname($real_hostname));
  if(strstr($hostname, ".vostron.net")) {
      list($first, $second, $third, $fourth) = explode(".", $address);
      $revzone = "$third.$second.$first.in-addr.arpa";
      $reverse = "$fourth.$revzone";
      $dnsname = "$interface.$hostname";
      $rev_sql = "SELECT `domain_id` FROM `domains` WHERE domain = '" . $revzone . "'";
      $rev_domain_id = @mysql_result(mysql_query($rev_sql, $dnsdblink),0);


   $rows_exist = mysql_result(mysql_query("SELECT COUNT(record_id) FROM `records` WHERE `host` = '$reverse'", $dnsdblink),0);

   if($rows_exist > '1') { $rows_exist = 1; echo("DELETE FROM `records` WHERE `host` = '$reverse' LIMIT $rows_exist;\n"); }

   if($address == $hostip) {
      if($rows_exist < '1') {
        $reverse_query  = "INSERT INTO `records` (`host`, `ttl`, `type`, `val`, `domain_id`) ";
        $reverse_query .= "VALUES ('$reverse','38400','P','$real_hostname','$rev_domain_id')";
      } else {
        $reverse_query  = "UPDATE `records` SET `val` = '$real_hostname' WHERE `host` = '".$reverse.".'";
      }
   } else {
     if($rows_exist < '1') {
        $reverse_query  = "INSERT INTO `records` (`host`, `ttl`, `type`, `val`, `domain_id`) ";
        $reverse_query .= "VALUES ('$reverse','38400','P','$dnsname','$rev_domain_id')";
      } else {
        $reverse_query  = "UPDATE `records` SET `val` = '".$dnsname.".' WHERE `host` = '".$reverse."'";
      }
   }

   if($rev_domain_id) {
      echo("$reverse_query; \n");
      #mysql_query($reverse_query, $dnsdblink);
   } else { 
      #echo("$hostname - $interface - $reverse FAILED\n");
   }

      $i = 1;
      unset($exist);
      while ($i <= count($zoneupdated)) {
        $thiszone = "$revzone";
        if ($zoneupdated[$i] == $thiszone) { $exist = "yes"; }
        $i++;
      }
      if(!$exist) { $zoneupdated[] = "$revzone"; }

      unset ($forward_query);

      if($address != $hostip) {

        $rows_exist = mysql_result(mysql_query("SELECT COUNT(record_id) FROM `records` WHERE `host` = '$dnsname'", $dnsdblink),0);
        if($rows_exist > '1') { $rows_exist--; echo("DELETE FROM `records` WHERE `host` = '$hostname' LIMIT $rows_exist;\n"); }

        if($rows_exist < '1') {
          $forward_query  = "INSERT INTO `records` (`host`, `ttl`, `type`, `val`, `domain_id`) ";
          $forward_query .= "VALUES ('$dnsname','38400','A','$address','381')";
        } elseif ($address != $hostip) {
          $forward_query  = "UPDATE `records` SET `val` = '$address' WHERE `host` = '$dnsname'";
        }
  
      }

      if($forward_query && $rev_domain_id) {
        echo("$forward_query; \n");
      }


      mysql_query($forward_query, $dnsdblink);
#      $i = 1;
#      unset($exist)
#      while ($i <= count($linkdone)) {
#        $thiszone = "$";
#        if ($zoneupdated[$i] == $thiszone) { $exist = "yes"; }
#        $i++;
#      }

  }
}

$i = 0;
while ($i < count($zoneupdated)) {

#  $sSQL = "update zones set name = '" . $name . "', ttl = " . $ttl . ", rdtype =
#  '" . $rdtype1 . "', rdata = '" . $rdata . "' where zoneid = " . $id;
#  $result = mysql_query($sSQL, $dnsdblink);

  $domain = $zoneupdated[$i];

  $sSQL = "select rdata, zoneid from zones where domain_name = '" . $domain . "' and rdtype = 'SOA'";
 # $result = mysql_query($sSQL, $dnsdblink);
#  $row = mysql_fetch_array($result);
  $soa = explode(" ", $row[0]);
  $year = substr($soa[2],0,4);
  $month = substr($soa[2],4,2);
  $day = substr($soa[2],6,2);
  $serial = substr($soa[2],8,2);
  $thisday = date("d",time());
  $thismonth = date("m",time());
  $thisyear = date("Y", time());
  if($day == $thisday && $month == $thismonth && $year = $thisyear) {
    $serial++;
  }
  else {
    $serial = "01";
  }
  $date = $thisyear . $thismonth . $thisday . sprintf("%02s",$serial);
  $rdata = $soa[0] . " " . $soa[1] . " " . $date . " " . $soa[3] . " " . $soa[4] . " " . $soa[5] . " " . $soa[6];
  $sSQL = "update zones set rdata = '" . $rdata . "' where zoneid = " . $row[1];
 # echo("$sSQL\n");
#  $result = mysql_query($sSQL, $dnsdblink);

  $i++;
}

?>
