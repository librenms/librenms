<?php

if ($device['os'] == 'roomalert3e') {
	echo(" ROOMALERT12E-MIB ");
	$description_oid	=	".1.3.6.1.4.1.20916.1.9.1.1.1.3.0";
	$description		=	snmp_get($device, $description_oid, '-Oqv', 'ROOMALERT12E-MIB', '+'.$config['install_dir'].'/mibs/avtech');
	$value_oid			=	".1.3.6.1.4.1.20916.1.9.1.1.1.1.0";
	$value				=	snmp_get($device, $value_oid, '-Oqv', 'ROOMALERT12E-MIB', '+'.$config['install_dir'].'/mibs/avtech');
	$divisor			=	100;
	$multiplier			=	1;
	if (is_numeric($value)) {
		discover_sensor($valid['sensor'], 'temperature', $device, $oid, 1, 'roomalert3e', $description, $divisor, $multiplier, null, null, null, null, $value);
	}
}
