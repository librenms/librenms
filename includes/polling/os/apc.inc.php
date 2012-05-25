<?php

# PDU
$serial = trim(snmp_get($device, "1.3.6.1.4.1.318.1.1.12.1.6.0", "-OQv", "", ""),'"');

if ($serial == "")
{
  # ATS
  $serial = trim(snmp_get($device, "1.3.6.1.4.1.318.1.1.8.1.6.0", "-OQv", "", ""),'"');
}

if ($serial == "")
{
  # UPS
  $serial = trim(snmp_get($device, "1.3.6.1.4.1.318.1.1.1.1.2.3.0", "-OQv", "", ""),'"');
}

if ($serial == "")
{
  # Masterswitch/AP9606
  $serial = trim(snmp_get($device, "1.3.6.1.4.1.318.1.1.4.1.5.0", "-OQv", "", ""),'"');
}

/////////////////////

# PDU
$hardware = trim(snmp_get($device, "1.3.6.1.4.1.318.1.1.12.1.5.0", "-OQv", "", ""),'"');
$hardware .= ' ' . trim(snmp_get($device, "1.3.6.1.4.1.318.1.1.12.1.2.0", "-OQv", "", ""),'"');

if ($hardware == " ")
{
  # ATS
  $hardware = trim(snmp_get($device, "1.3.6.1.4.1.318.1.1.8.1.5.0", "-OQv", "", ""),'"');
  $hardware .= ' ' . trim(snmp_get($device, "1.3.6.1.4.1.318.1.1.8.1.1.0", "-OQv", "", ""),'"');
}

if ($hardware == " ")
{
  # UPS
  $hardware = trim(snmp_get($device, "1.3.6.1.4.1.318.1.1.1.1.1.1.0", "-OQv", "", ""),'"');
  $hardware .= ' ' . trim(snmp_get($device, "1.3.6.1.4.1.318.1.1.1.1.2.1.0", "-OQv", "", ""),'"');
}

if ($hardware == " ")
{
  # Masterswitch/AP9606
  $hardware = trim(snmp_get($device, "1.3.6.1.4.1.318.1.1.4.1.4.0", "-OQv", "", ""),'"');
  $hardware .= ' ' . trim(snmp_get($device, "1.3.6.1.4.1.318.1.1.4.1.1.0", "-OQv", "", ""),'"');
}

if ($hardware == " ")
{
  # InRow chiller
  $hardware = trim(snmp_get($device, "1.3.6.1.4.1.318.1.1.13.3.2.2.1.4.0", "-OQv", "", ""),'"');
  $hardware .= ' ' . trim(snmp_get($device, ".1.3.6.1.4.1.318.1.1.13.3.2.2.1.7.0", "-OQv", "", ""),'"');
}

/////////////////////

$AOSrev = trim(snmp_get($device, "1.3.6.1.4.1.318.1.4.2.4.1.4.1", "-OQv", "", ""),'"');
$APPrev = trim(snmp_get($device, "1.3.6.1.4.1.318.1.4.2.4.1.4.2", "-OQv", "", ""),'"');

if ($AOSrev == '')
{
  # PDU
  $version = trim(snmp_get($device, "1.3.6.1.4.1.318.1.1.12.1.3.0", "-OQv", "", ""),'"');

  if ($version == "")
  {
    # ATS
    $version = trim(snmp_get($device, "1.3.6.1.4.1.318.1.1.8.1.2.0", "-OQv", "", ""),'"');
  }

  if ($version == "")
  {
    # Masterswitch/AP9606
    $version = trim(snmp_get($device, "1.3.6.1.4.1.318.1.1.4.1.2.0", "-OQv", "", ""),'"');
  }
}
else
{
  $version = "AOS $AOSrev / App $APPrev";
}

?>