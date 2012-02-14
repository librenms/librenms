<?php

### Allied Telesis have somewhat messy MIBs. It's often hard to work out what is where. :)

$hardware = snmp_get($device, "atiswitchProductType.0", "-OsvQU", "+AtiSwitch-MIB");
$version  = snmp_get($device, "atiswitchSwVersion.0", "-OsvQU", "+AtiSwitch-MIB");
$software = snmp_get($device, "atiswitchSw.0", "-OsvQU", "+AtiSwitch-MIB");

if ($software && $version)
{
  $version = $software . " " . $version;
}

# sysDescr.0 = STRING: "Allied Telesis AT-8624T/2M version 2.9.1-13 11-Dec-2007"
# sysDescr.0 = STRING: "Allied Telesyn Ethernet Switch AT-8012M"
# sysDescr.0 = STRING: "ATI AT-8000S" <------------------------------------- RADLAN ********
# sysDescr.0 = STRING: "Allied Telesyn AT-8624T/2M version 2.8.1-02 05-Sep-2006"
# sysDescr.0 = STRING: "AT-8126XL, AT-S21 version 1.4.2"

# AtiL2-MIB::atiL2SwProduct.0 = STRING: "AT-8326GB"
# AtiL2-MIB::atiL2SwVersion.0 = STRING: "AT-S41 v1.1.6 "

if (!$hardware && !$version && !$features)
{
  $hardware = snmp_get($device, "atiL2SwProduct.0", "-OsvQU", "+AtiL2-MIB");
  $version = snmp_get($device, "atiL2SwVersion.0", "-OsvQU", "+AtiL2-MIB");
}

#Allied Telesyn AT-8948 version 2.7.4-02 22-Aug-2005

list($a,$b,$c,$d,$e,$f) = explode(" ", $poll_device['sysDescr']);

if (!$hardware && !$version && !$features)
{
  if ($a == "Allied" && $d == "version")
  {
    $version = $e;
    $features = $f;
    $hardware = $c;
  }
}

if ($a == "Allied" && $d == "Switch")
{
  $hardware = $e;
}

$version = str_replace("\"","", $version);
$features = str_replace("\"","", $features);
$hardware = str_replace("\"","", $hardware);

?>