<?php

$Proneer_portDesc = SnmpQuery::walk('ADVANTECH-EKI-PRONEER-MIB::description')->table(1);

foreach ($Proneer_portDesc as $index => $portDesc) {
    $port_stats[$index]['ifAlias'] = $portDesc['ADVANTECH-EKI-PRONEER-MIB::description'];
}

unset($Proneer_portDesc);
