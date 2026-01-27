<?php

d_echo('');
d_echo('/opt/librenms/includes/discovery/mx-nos.inc.php');
d_echo('--Start--');

$PortMib_port = SnmpQuery::walk('MOXA-PORT-MIB::portConfigDescription')->table(1);

foreach ($PortMib_port as $index => $moxaport) {
    $port_stats[$index]['ifAlias'] = $moxaport['MOXA-PORT-MIB::portConfigDescription'];
}

unset($PortMib_port);

d_echo('');
d_echo('--End--');
