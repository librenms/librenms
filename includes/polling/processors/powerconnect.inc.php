<?php

# "5 Sec (7.31%),    1 Min (14.46%),   5 Min (10.90%)"

$values = trim(snmp_get($device, "dellLanExtension.6132.1.1.1.1.4.4.0", "-OvQ", "Dell-Vendor-MIB"),'"');

preg_match('/5 Sec \((.*)%\),.*1 Min \((.*)%\),.*5 Min \((.*)%\)$/', $values, $matches);

$proc = $matches[3];

?>
