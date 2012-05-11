<?php

# Discover ports

echo("Ports : ");

$ports = array();
$ports = snmpwalk_cache_oid($device, "ifDescr", $ports, "IF-MIB");
#$ports = snmpwalk_cache_oid($device, "ifName", $ports, "IF-MIB");
#$ports = snmpwalk_cache_oid($device, "ifType", $ports, "IF-MIB");

$interface_ignored = 0;
$interface_added   = 0;

foreach ($ports as $ifIndex => $port)
{
  if (is_port_valid($port, $device))
  {
    if ($device['os'] == "vmware" && preg_match("/Device ([a-z0-9]+) at .*/", $port['ifDescr'], $matches)) { $port['ifDescr'] = $matches[1]; }
    $port['ifDescr'] = fixifName($port['ifDescr']);
    if ($debug) echo("\n $if ");
    if (mysql_result(mysql_query("SELECT COUNT(*) FROM `ports` WHERE `device_id` = '".$device['device_id']."' AND `ifIndex` = '$ifIndex'"), 0) == '0')
    {
      mysql_query("INSERT INTO `ports` (`device_id`,`ifIndex`,`ifDescr`) VALUES ('".$device['device_id']."','$ifIndex','".mres($port['ifDescr'])."')");
      # Add Interface
      echo("+");
    } else {
      mysql_query("UPDATE `ports` SET `deleted` = '0' WHERE `device_id` = '".$device['device_id']."' AND `ifIndex` = '$ifIndex'");
      echo(".");
    }
    $int_exists[] = "$ifIndex";
  } else {
    # Ignored Interface
    if (mysql_result(mysql_query("SELECT COUNT(*) FROM `ports` WHERE `device_id` = '".$device['device_id']."' AND `ifIndex` = '$ifIndex'"), 0) != '0')
    {
      mysql_query("UPDATE `ports` SET `deleted` = '1' WHERE `device_id` = '".$device['device_id']."' AND `ifIndex` = '$ifIndex'");
      # Delete Interface
      echo("-"); ## Deleted Interface
    } else {
      echo("X"); ## Ignored Interface
    }
  }
}

$sql = "SELECT * FROM `ports` WHERE `device_id`  = '".$device['device_id']."' AND `deleted` = '0'";
$query = mysql_query($sql);

while ($test_if = mysql_fetch_assoc($query))
{
  unset($exists);
  $i = 0;
  while ($i < count($int_exists) && !isset($exists))
  {
    $this_if = $test_if['ifIndex'];
    if ($int_exists[$i] == $this_if) { $exists = 1; }
    $i++;
  }
  if (!$exists)
  {
    echo("-");
    mysql_query("UPDATE `ports` SET `deleted` = '1' WHERE interface_id = '" . $test_if['interface_id'] . "'");
  }
}

unset($temp_exists);
echo("\n");

?>
