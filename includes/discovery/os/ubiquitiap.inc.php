<?php

if (!$os || $os == 'linux') {
    if (stristr(snmp_get($device, "dot11manufacturerName.5", "-Oqv", "IEEE802dot11-MIB"),"Ubiquiti Networks") || stristr(snmp_get($device, "dot11manufacturerName.6", "-Oqv", "IEEE802dot11-MIB"),"Ubiquiti Networks")) {
        $os = "ubiquitiap";
    }
}

?>
