<?php

d_echo('');
d_echo('/opt/librenms/includes/polling/ports/os/advantech.inc.php');
d_echo('--Start--');

$Proneer_portDesc = SnmpQuery::walk('ADVANTECH-EKI-PRONEER-MIB::description')->table(1);

foreach ($Proneer_portDesc as $index => $portDesc) {
    $port_stats[$index]['ifAlias'] = $portDesc['ADVANTECH-EKI-PRONEER-MIB::description'];
}

unset($Proneer_portDesc);

d_echo('');
d_echo('--End--');
