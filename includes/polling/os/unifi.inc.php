<?php
$data = snmp_get_multi($device, "dot11manufacturerProductName.2 dot11manufacturerProductVersion.2", "-OQUs", "IEEE802dot11-MIB");
if (empty($data)) {
    $data = snmp_get_multi($device, "dot11manufacturerProductName.3 dot11manufacturerProductVersion.3", "-OQUs", "IEEE802dot11-MIB");
}
if (empty($data)) {
    $data = snmp_get_multi($device, "dot11manufacturerProductName.4 dot11manufacturerProductVersion.4", "-OQUs", "IEEE802dot11-MIB");
}
if (empty($data)) {
    $data = snmp_get_multi($device, "dot11manufacturerProductName.5 dot11manufacturerProductVersion.5", "-OQUs", "IEEE802dot11-MIB");
}
if (empty($data)) {
    $data = snmp_get_multi($device, "dot11manufacturerProductName.6 dot11manufacturerProductVersion.6", "-OQUs", "IEEE802dot11-MIB");
}

foreach ($data as $line) {
    if (!empty($line['dot11manufacturerProductName'])) {
        $hardware = $line['dot11manufacturerProductName'];
    }
    if (preg_match("/(v[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", $line['dot11manufacturerProductVersion'],$matches)) {
        $version = $matches[0];
    }
}

