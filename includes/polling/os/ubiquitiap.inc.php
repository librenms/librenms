<?php

$data = snmp_get_multi($device, "dot11manufacturerProductName.5 dot11manufacturerProductVersion.5", "-Oqv", "IEEE802dot11-MIB");
if (empty($data)) {
    $data = snmp_get_multi($device, "dot11manufacturerProductName.6 dot11manufacturerProductVersion.6", "-Oqv", "IEEE802dot11-MIB");
}
$hardware = $data[0];

if (preg_match("/(v[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", $data[1],$matches)) {
    $version = $matches[0];
}

?>
