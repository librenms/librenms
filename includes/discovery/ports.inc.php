<?php

# Discover ports

echo("Ports : ");

/// Loop database and build a little cache to reduce db hits.
foreach(dbFetchRows("SELECT * FROM `ports` WHERE `device_id` = ?", array($device['device_id'])) as $port)
{
  $ports_db[$port['ifIndex']] = $port;
  $ports_l[$port['ifIndex']] = $port['interface_id'];
}

#print_r($ports_db);

$ports = array();
$ports = snmpwalk_cache_oid($device, "ifDescr", $ports, "IF-MIB");
$ports = snmpwalk_cache_oid($device, "ifName", $ports, "IF-MIB");
$ports = snmpwalk_cache_oid($device, "ifType", $ports, "IF-MIB");

### New interface detection
foreach ($ports as $ifIndex => $port)
{
  /// Check the port against our filters.
  if (is_port_valid($port, $device))
  {
    if (!is_array($ports_db[$ifIndex]))
    {
      $interface_id = dbInsert(array('device_id' => $device['device_id'], 'ifIndex' => $ifIndex), 'ports');
      echo("Adding: ".$port['ifName']."(".$ifIndex.")(".$ports_db[$port['ifIndex']]['interface_id'].")");
    } elseif ($ports_db[$ifIndex]['deleted'] == "1") {
      dbUpdate(array('deleted' => '0'), 'ports', '`interface_id` = ?', array($ports_db[$ifIndex]['interface_id']));
      $ports_db[$ifIndex]['deleted'] = "0";
      echo("U");
    } else {
      echo (".");
    }
    /// We've seen it. Remove it from the cache.
    unset($ports_l[$ifIndex]);
  } else {
    if (is_array($ports_db[$port['ifIndex']])) {
      if ($ports_db[$port['ifIndex']]['deleted'] != "1")
      {
        dbUpdate(array('deleted' => '1'), 'ports', '`interface_id` = ?', array($ports_db[$ifIndex]['interface_id']));
        $ports_db[$ifIndex]['deleted'] = "1";
      }
    }
    echo("X");
  }
}
### End New interface detection

### If it's in our $ports_l list, that means it's not been seen. Mark it deleted.

foreach($ports_l as $ifIndex => $port_id)
{
  if($ports_db[$ifIndex]['deleted'] == "0")
  {
    dbUpdate(array('deleted' => '1'), 'ports', '`interface_id` = ?', array($port_id));
    echo("-".$ifIndex);
  }
}

echo("\n");

?>
