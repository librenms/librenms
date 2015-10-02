<?php

if ($device['os'] == 'roomalert3e') {
	echo(" ROOMALERT12E-MIB ");
	$descr = "Temperature";
	$oid = ".1.3.6.1.4.1.20916.1.9.1.1.1.1.0";
	$value   = snmp_get($device, $oid, '-Oqv', 'ROOMALERT12E-MIB', '+'.$config['install_dir'].'/mibs/avtech');
	$divisor = 1;
	$multiplier = 1;
	if (is_numeric($value)) {
		if ($value > 100) { $divisor = 100 }
		discover_sensor($valid['sensor'], 'temperature', $device, $oid, 1, 'roomalert3e', $descr, $divisor, $multiplier, null, null, null, null, $value);
	}
}
