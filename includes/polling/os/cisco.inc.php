<?php

if (preg_match('/^Cisco IOS Software, .+? Software \([^\-]+-([\w\d]+)-\w\), Version ([^,]+)/', $poll_device['sysDescr'], $regexp_result))
{
  $features = $regexp_result[1];
  $version = $regexp_result[2];
}
elseif( false )
{
  # Placeholder
  # Other regexp for other type of string
}

echo("\n".$poll_device['sysDescr']."\n");

$oids = "entPhysicalModelName.1 entPhysicalContainedIn.1 entPhysicalName.1 entPhysicalSoftwareRev.1 entPhysicalModelName.1001 entPhysicalContainedIn.1001 cardDescr.1 cardSlotNumber.1";

$data = snmp_get_multi($device, $oids, "-OQUs", "ENTITY-MIB:OLD-CISCO-CHASSIS-MIB");

if ($data[1]['entPhysicalContainedIn'] == "0")
{
  if (!empty($data[1]['entPhysicalSoftwareRev']))
  {
    $version = $data[1]['entPhysicalSoftwareRev'];
  }

  if (!empty($data[1]['entPhysicalName']))
  {
    $hardware = $data[1]['entPhysicalName'];
  }

  if (!empty($data[1]['entPhysicalModelName']))
  {
    $hardware = $data[1]['entPhysicalModelName'];
  }
}

#   if ($slot_1 == "-1" && strpos($descr_1, "No") === FALSE) { $ciscomodel = $descr_1; }
#   if (($contained_1 == "0" || $name_1 == "Chassis") && strpos($model_1, "No") === FALSE) { $ciscomodel = $model_1; list($version_1) = explode(",",$ver_1); }
#   if ($contained_1001 == "0" && strpos($model_1001, "No") === FALSE) { $ciscomodel = $model_1001; }
#   $ciscomodel = str_replace("\"","",$ciscomodel);
#   if ($ciscomodel) { $hardware = $ciscomodel; unset($ciscomodel); }

if(empty($hardware)) {   $hardware = snmp_get($device, "sysObjectID.0", "-Osqv", "SNMPv2-MIB:CISCO-PRODUCTS-MIB"); }

#if(isset($cisco_hardware_oids[$poll_device['sysObjectID']])) { $hardware = $cisco_hardware_oids[$poll_device['sysObjectID']]; }

?>
