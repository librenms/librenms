<?php

# PDU
$serial = trim(snmp_get($device, "1.3.6.1.4.1.318.1.1.12.1.6.0", "-OQv", "", ""),'"');

if ($serial == "")
{
  # ATS
  $serial = trim(snmp_get($device, "1.3.6.1.4.1.318.1.1.8.1.6.0", "-OQv", "", ""),'"');
}

######################

# PDU
$hardware = trim(snmp_get($device, "1.3.6.1.4.1.318.1.1.12.1.5.0", "-OQv", "", ""),'"');
$hardware .= ' ' . trim(snmp_get($device, "1.3.6.1.4.1.318.1.1.12.1.2.0", "-OQv", "", ""),'"');

if ($hardware == " ")
{
  # ATS
  $hardware = trim(snmp_get($device, "1.3.6.1.4.1.318.1.1.8.1.5.0", "-OQv", "", ""),'"');
  $hardware .= ' ' . trim(snmp_get($device, "1.3.6.1.4.1.318.1.1.8.1.1.0", "-OQv", "", ""),'"');
}

######################

# PDU
$version = trim(snmp_get($device, "1.3.6.1.4.1.318.1.1.12.1.3.0", "-OQv", "", ""),'"');

if ($version == "")
{
  # ATS
  $version = trim(snmp_get($device, "1.3.6.1.4.1.318.1.1.8.1.2.0", "-OQv", "", ""),'"');
}

?>
