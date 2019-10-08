<?php
if (strpos($device['sysDescr'],'Enterprise'))
	list(,,$hardware,$version) = explode(' ', $device['sysDescr']);
elseif (strpos($device['sysObjectID'],'1.3.6.1.4.1.6486.800.1.1.2.2.4')) { 
	$hardware = snmp_get($device, '.1.3.6.1.4.1.89.53.4.1.6.1', '-Osqv', 'RADLAN-Physicaldescription-MIB'); //RADLAN-Physicaldescription-MIB::rlPhdStackProductID
	$version = snmp_get($device, '.1.3.6.1.4.1.89.53.14.1.2.1', '-Osqv', 'RADLAN-Physicaldescription-MIB'); //RADLAN-Physicaldescription-MIB::rlPhdUnitGenParamSoftwareVersion
	}
elseif (($device['sysObjectID']==".1.3.6.1.4.1.6486.800.1.1.2.1.10.1.2")) 
	list($hardware,$version,) = explode(' ',"OS6424 " . $device['sysDescr']);
else
	list(,$hardware,$version) = explode(' ', $device['sysDescr']);

