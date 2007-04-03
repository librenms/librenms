#!/usr/bin/php
<?

include("config.php");

$query = "SELECT * FROM temperature AS T, devices AS D WHERE T.temp_host = D.id AND D.status = '1'";
$data = mysql_query($query);
while($entry = mysql_fetch_array($data)) {

  $community = $entry[community];
  $hostname = $entry[hostname];
  $snmpver = $entry[snmpver];

  $temp = `snmpget -O qv -$snmpver -c $community $hostname $entry[temp_oid]`;

  $temprrd  = "rrd/" . $entry[hostname] . "-temp" . $entry[temp_id] . ".rrd";
  if (!is_file($temprrd)) {
    `rrdtool create $temprrd \
     --step 300 \
     DS:temp:GAUGE:600:-273:1000 \
     RRA:AVERAGE:0.5:1:1200 \
     RRA:MIN:0.5:12:2400 \
     RRA:MAX:0.5:12:2400 \
     RRA:AVERAGE:0.5:12:2400`;
  }

  $temp = str_replace("\"", "", $temp);
  echo("$temprrd, N:$temp");

  `rrdtool update $temprrd N:$temp`;

  mysql_query("UPDATE temperature SET temp_current = '$temp' WHERE temp_id = '$entry[temp_id]'");

}

?>
