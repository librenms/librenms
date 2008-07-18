<?php

$jun_ver =  trim(str_replace("\"", "", shell_exec($config['snmpget'] . " -".$device['snmpver']." -Oqv -c ".$device['community']." ".$device['hostname']." .1.3.6.1.2.1.25.6.3.1.2.2")));
$hardware = trim(str_replace("\"", "", shell_exec($config['snmpget'] . " -".$device['snmpver']." -Oqv -c ".$device['community']." ".$device['hostname']." .1.3.6.1.4.1.2636.3.1.2.0")));
$serial = trim(str_replace("\"", "", shell_exec($config['snmpget'] . " -".$device['snmpver']." -Oqv -c ".$device['community']." ".$device['hostname']." .1.3.6.1.4.1.2636.3.1.3.0")));

$version = preg_replace("/.+\[(.+)\].+/", "\\1", $jun_ver);
$features = preg_replace("/.+\ \((.+)\)$/", "\\1", $jun_ver);

echo("$hardware - $version - $features - $serial\n");



?>
