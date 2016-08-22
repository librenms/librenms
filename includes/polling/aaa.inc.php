<?php
$name = 'aaa';
if ($device['os'] === 'routeros') {
	$sessions = trim(snmp_get($device, 'casnActiveTableEntries.0', '-Ovq', 'CISCO-AAA-SESSION-MIB'));
	if($sessions > 0) {
		$rrd_name = array('aaa','count');
		$rrd_def = 'DS:online:GAUGE:600:0:50000';
		$fields = array (
			'online' => $sessions
			);
		$tags = compact('name', 'rrd_name', 'rrd_def');
		data_update($device, 'aaa', $tags, $fields);
	}
}
