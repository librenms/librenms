#!/usr/bin/php
<?

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('log_errors', 1);
ini_set('error_reporting', E_ALL);


include("config.php");
include("includes/functions.php");
include("includes/cdp.inc.php");

$start = utime();

### Observer Device Discovery

echo("Observer v".$config['version']." MAC Accounting Discovery\n\n");

mysql_query("DELETE FROM mac_accounting WHERE 1");

$data = trim(`cat mac-accounting.txt`);

foreach( explode("\n", $data) as $peer_entry) {
 list($interface_ip, $peer_ip, $peer_desc, $peer_asn, $peer_mac, $in_oid, $out_oid) = explode(",", $peer_entry);
 $interface_id = mysql_result(mysql_query("SELECT interface_id FROM ipaddr WHERE addr = '$interface_ip'"),0);
 $device_id = mysql_result(mysql_query("SELECT device_id FROM interfaces WHERE interface_id = '$interface_id'"),0);

 echo("PEER : $peer_ip AS$peer_asn ($peer_mac) int: $interface_id host: $device_id \n");

 mysql_query("INSERT INTO `mac_accounting` (interface_id, peer_ip, peer_desc, peer_asn, peer_mac, in_oid, out_oid) VALUES ('$interface_id','$peer_ip','$peer_desc','$peer_asn','$peer_mac','$in_oid','$out_oid')");

}


?>
