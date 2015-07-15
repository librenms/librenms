<?php

$canopy_type = snmp_get($device,"boxDeviceType.0", "-Oqv", "WHISP-BOX-MIBV2-MIB");

if (stristr($canopy_type,"MIMO OFDM")) {
    $hardware = 'PMP 450';
} elseif (stristr($canopy_type,"OFDM")) {
    $hardware = 'PMP 430';
} else {
    $hardware = 'PMP 100';
}
