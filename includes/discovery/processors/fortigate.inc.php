<?php

//
// Somewhat dynamic processor discovery for Fortigate boxes.  
//
// FORTINET-FORTIGATE-MIB::fgSysCpuUsage.X where X is the CPU number
// FORTINET-FORTIGATE-MIB::fgProcModDescr.X where X is the CPU number
// FORTINET-FORTIGATE-MIB::fgProcessorCount.0 -> Num CPUs in the device

if ($device['os'] == 'fortigate') {
	echo 'Fortigate : ';

// Forti have logical CPU numbering - start at 1 and increment to $num_cpu in the box. 
$num_cpu   = snmp_get($device, 'FORTINET-FORTIGATE-MIB::fgProcessorCount.0', '-Ovq');

print "Forti-found $num_cpu CPUs\n";

for($i = 1; $i <= $num_cpu; $i++) {
	// HERP DERP IM A FORTIGATE AND I PUT NON NUMERIC VALUES IN A GAUGE 
	$cpu_usage = snmp_get($device, "FORTINET-FORTIGATE-MIB::fgProcessorUsage.$i", '-Ovq');
	$usage = trim ( str_replace(" %", "", $cpu_usage ) ) ; 
	$descr = snmp_get($device, "FORTINET-FORTIGATE-MIB::fgProcModDescr.$i", '-Ovq');
	print "CPU: $num_cpu - USAGE: $cpu_usage - TYPE $descr\n";
	if (is_numeric($usage)) {
		discover_processor($valid['processor'], $device, "FORTINET-FORTIGATE-MIB::fgProcessorUsage." . $num_cpu, '0', 'fortigate-fixed', $descr, '1', $usage, null, null);
	}
} // END For loop for CPU discovery

} // END if device is Fortigate

unset($processors_array);
