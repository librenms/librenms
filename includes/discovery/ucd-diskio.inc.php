<?php

echo("UCD Disk IO : ");
$diskio_array = snmpwalk_cache_oid($device, "diskIOEntry", array(), "UCD-DISKIO-MIB" , "+".$config['install_dir']."/mibs/");
$valid_diskio = array();
#  if ($debug) { print_r($diskio_array); }

if (is_array($diskio_array))
{
  foreach ($diskio_array as $index => $entry)
  {
    if ($entry['diskIONRead'] > "0" || $entry['diskIONWritten'] > "0")
    {
      if ($debug) { echo("$index ".$entry['diskIODevice']."\n"); }

      if (mysql_result(mysql_query("SELECT COUNT(*) FROM `ucd_diskio` WHERE `device_id` = '".$device['device_id']."' AND `diskio_index` = '".$index."'"),0) == "0")
      {
        $sql = "INSERT INTO `ucd_diskio` (`device_id`,`diskio_index`,`diskio_descr`) VALUES ('".$device['device_id']."','".$index."','".$entry['diskIODevice']."')";
        mysql_query($sql); echo("+");
        if ($debug) { echo($sql . " - " . mysql_affected_rows() . "inserted "); }
      }
      else
      {
        echo(".");
        /// FIXME Need update code here!
      }

      $valid_diskio[$index] = 1;
    } /// end validity check
  } /// end array foreach
} /// End array if

/// Remove diskio entries which weren't redetected here

$sql = "SELECT * FROM `ucd_diskio` where `device_id`  = '".$device['device_id']."'";
$query = mysql_query($sql);

if ($debug) { print_r ($valid_diskio); }

while ($test = mysql_fetch_assoc($query))
{
  if ($debug) { echo($test['diskio_index'] . " -> " . $test['diskio_descr'] . "\n"); }
  if (!$valid_diskio[$test['diskio_index']])
  {
    echo("-");
    mysql_query("DELETE FROM `ucd_diskio` WHERE `diskio_id` = '" . $test['diskio_id'] . "'");
  }
}

unset($valid_diskio);
echo("\n");

?>
