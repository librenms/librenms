<?php

# FIXME snmp_get
$snmp_cmd =  $config['snmpget'] . " -M ".$config['mibdir'] . " -m Dell-Vendor-MIB -O Qv -" . $device['snmpver'] . " -c " . $device['community'] . " " .
                 $device['hostname'].":".$device['port'];
$snmp_cmd .= " productIdentificationDisplayName.0 productIdentificationVersion.0 productIdentificationDescription.0";

list($hardware, $version, $features) = explode("\n", shell_exec($snmp_cmd));

if (strstr($hardware,"No Such Object available"))
{
  $hardware = $sysDescr;
}

?>