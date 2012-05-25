<?php

/// Build SNMP Cache Array

$port_stats = array();
$port_stats = snmpwalk_cache_oid($device, "ifDescr", $port_stats, "IF-MIB");
$port_stats = snmpwalk_cache_oid($device, "ifName", $port_stats, "IF-MIB");
$port_stats = snmpwalk_cache_oid($device, "ifType", $port_stats, "IF-MIB");

/// End Building SNMP Cache Array

if ($debug) { print_r($port_stats); }

/// Build array of ports in the database

/// FIXME -- this stuff is a little messy, looping the array to make an array just seems wrong. :>
///       -- i can make it a function, so that you don't know what it's doing.
///       -- $ports_db = adamasMagicFunction($ports_db); ?

foreach (dbFetchRows("SELECT * FROM `ports` WHERE `device_id` = ?", array($device['device_id'])) as $port)
{
  $ports_db[$port['ifIndex']] = $port;
  $ports_db_l[$port['ifIndex']] = $port['port_id'];
}

/// New interface detection
foreach ($port_stats as $ifIndex => $port)
{
  /// Check the port against our filters.
  if (is_port_valid($port, $device))
  {
    if (!is_array($ports_db[$ifIndex]))
    {
      $port_id = dbInsert(array('device_id' => $device['device_id'], 'ifIndex' => $ifIndex), 'ports');
      $ports_db[$ifIndex] = dbFetchRow("SELECT * FROM `ports` WHERE `device_id` = ? AND `ifIndex` = ?", array($device['device_id'], $ifIndex));
      echo("Adding: ".$port['ifName']."(".$ifIndex.")(".$ports_db[$port['ifIndex']]['port_id'].")");
    } elseif ($ports_db[$ifIndex]['deleted'] == "1") {
      dbUpdate(array('deleted' => '0'), 'ports', '`port_id` = ?', array($ports_db[$ifIndex]['port_id']));
      $ports_db[$ifIndex]['deleted'] = "0";
      echo("U");
    } else {
      echo(".");
    }
    /// We've seen it. Remove it from the cache.
    unset($ports_l[$ifIndex]);
  } else {
    if (is_array($ports_db[$port['ifIndex']])) {
      if ($ports_db[$port['ifIndex']]['deleted'] != "1")
      {
        dbUpdate(array('deleted' => '1'), 'ports', '`port_id` = ?', array($ports_db[$ifIndex]['port_id']));
        $ports_db[$ifIndex]['deleted'] = "1";
        echo("-");
      }
    }
    echo("X");
  }
}
/// End New interface detection

/// Interface Deletion
/// If it's in our $ports_l list, that means it's not been seen. Mark it deleted.
foreach ($ports_l as $ifIndex => $port_id)
{
  if ($ports_db[$ifIndex]['deleted'] == "0")
  {
    dbUpdate(array('deleted' => '1'), 'ports', '`port_id` = ?', array($port_id));
    echo("-".$ifIndex);
  }
}
/// End interface deletion
echo("\n");

/// Clear Variables Here
unset($port_stats);
unset($ports_db);
unset($ports_db_db);

?>
