<?php
$fwModuleState = snmp_getnext($device, 'fwModuleState', '-Ovq', 'CHECKPOINT-MIB');
$mgActiveStatus = snmp_getnext($device, 'mgActiveStatus', '-Ovq', 'CHECKPOINT-MIB');

//Check if is a Gaia Security Gateway
if ($fwModuleState == "Installed" || $fwModuleState == "Not Installed") {
	echo 'Gaia Security Gateway Count: ';
		$connections = [
			'Number of concurrent connections' => ['.1.3.6.1.4.1.2620.1.1.25.3', 'fwNumConn'],  //CHECKPOINT-MIB::fwNumConn.0
			'Peak number of concurrent connections' => ['.1.3.6.1.4.1.2620.1.1.25.4', 'fwPeakNumConn'],  //CHECKPOINT-MIB::fwPeakNumConn.0
			'Limit of Connections table' => ['.1.3.6.1.4.1.2620.1.1.25.10', 'fwConnTableLimit'],  //CHECKPOINT-MIB::fwConnTableLimit.0
			'Connections rate' => ['.1.3.6.1.4.1.2620.1.1.26.11.6', 'fwConnectionsStatConnectionRate'],  //CHECKPOINT-MIB::fwConnectionsStatConnectionRate.0
			'Number of connections handled by SecureXL' => ['.1.3.6.1.4.1.2620.1.36.1.2', 'fwSXLConnsExisting'],  //CHECKPOINT-MIB::fwSXLConnsExisting.0
		];
		
		foreach ($connections as $descr => $oid) {
			$oid_num = $oid[0];
			$oid_txt = $oid[1];
			$group = 'Connections';
			$result = snmp_getnext($device, $oid_txt, '-Ovq', 'CHECKPOINT-MIB');
			$result = str_replace(' Sessions Per Second', '', $result);
		
			discover_sensor(
				$valid['sensor'],
				'count',
				$device,
				$oid_num . '.0',
				$oid_txt . '.0',
				'sessions',
				$descr,
				1,
				1,
				null,
				null,
				null,
				null,
				$result,
				'snmp',
				null,
				null,
				null,
				$group		
			);
		}
		
		$vpn = [
			'Number of IKE current SAs' => ['.1.3.6.1.4.1.2620.1.2.9.1.1', 'cpvIKECurrSAs'],  //CHECKPOINT-MIB::cpvIKECurrSAs.0
			'Number of IPsec current Inbound ESP SAs' => ['.1.3.6.1.4.1.2620.1.2.5.2.1', 'cpvCurrEspSAsIn'],  //CHECKPOINT-MIB::cpvCurrEspSAsIn.0
			'Number of IPsec current Outbound ESP SAs' => ['.1.3.6.1.4.1.2620.1.2.5.2.3', 'cpvCurrEspSAsOut'],  //CHECKPOINT-MIB::cpvCurrEspSAsOut.0
		];
		
		foreach ($vpn as $descr => $oid) {
			$oid_num = $oid[0];
			$oid_txt = $oid[1];
			$group = 'VPN';
			$result = snmp_getnext($device, $oid_txt, '-Ovq', 'CHECKPOINT-MIB');
			$result = str_replace(' Sessions Per Second', '', $result);
		
			discover_sensor(
				$valid['sensor'],
				'count',
				$device,
				$oid_num . '.0',
				$oid_txt . '.0',
				'sessions',
				$descr,
				1,
				1,
				null,
				null,
				null,
				null,
				$result,
				'snmp',
				null,
				null,
				null,
				$group
			);
		}
}
//Check if is a Gaia Management Server
if ($mgActiveStatus == 'active') {
	echo 'Gaia Management Server Count: ';
		$connections = [
			'Log Receive Rate' => ['.1.3.6.1.4.1.2620.1.7.14.1', 'mgLSLogReceiveRate'],  //CHECKPOINT-MIB::mgLSLogReceiveRate
			'Log Receive Rate Peak' => ['.1.3.6.1.4.1.2620.1.7.14.2', 'mgLSLogReceiveRatePeak'],  //CHECKPOINT-MIB::mgLSLogReceiveRatePeak
		];
		
		foreach ($connections as $descr => $oid) {
			$oid_num = $oid[0];
			$oid_txt = $oid[1];
			$group = 'Connections';
			$result = snmp_getnext($device, $oid_txt, '-Ovq', 'CHECKPOINT-MIB');
			$result = str_replace(' Sessions Per Second', '', $result);
		
			discover_sensor(
				$valid['sensor'],
				'count',
				$device,
				$oid_num . '.0',
				$oid_txt . '.0',
				'sessions',
				$descr,
				1,
				1,
				null,
				null,
				null,
				null,
				$result,
				'snmp',
				null,
				null,
				null,
				$group		
			);
		}
}
