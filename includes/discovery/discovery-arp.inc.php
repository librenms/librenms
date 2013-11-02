<?php
//
// Observium module to do device discovery by ARP table contents.
//
// Needs to be run after the ARP table discovery, because it uses the
// data gathered by the ARP table discovery module.  Keeps a cache of
// seen hosts, and will not attempt re-discovery of the same IP (whether
// discovery failed or succeed) during the same discovery run.
//
// Copyright (c) 2012-2013 Gear Consulting Pty Ltd <http://libertysys.com.au/>
//
// Author:  Paul Gear <librenms@libertysys.com.au>
// License: GPLv3
//

include_once("../../includes/print-interface.inc.php");

echo("ARP Discovery: ");

$hostname = $device['hostname'];
$deviceid = $device['device_id'];

// Find all IPv4 addresses in the MAC table that haven't been discovered on monitored devices.
$sql = "
SELECT *
FROM ipv4_mac as m, ports as i
WHERE m.port_id = i.port_id
    AND i.device_id = ?
    AND i.deleted = 0
    AND NOT EXISTS (
	SELECT * FROM ipv4_addresses a
	WHERE a.ipv4_address = m.ipv4_address
    )
GROUP BY ipv4_address
ORDER BY ipv4_address
";

// FIXME: Observium now uses ip_mac.ip_address in place of ipv4_mac.ipv4_address - why?

unset($names);
unset($ips);

foreach (dbFetchRows($sql, array($deviceid)) as $entry)
{
	global $config;

	$ip = $entry['ip_address'];
	$mac = $entry['mac_address'];
	$if = $entry['port_id'];
	$int = humanize_port($if);
	$label = $int['label'];

	// Even though match_network is done inside discover_new_device, we do it here
	// as well in order to skip unnecessary reverse DNS lookups on discovered IPs.
	if (match_network($config['autodiscovery']['nets-exclude'], $ip)) {
		echo("x");
		continue;
	}
	if (!match_network($config['nets'], $ip)) {
		echo("i");
		log_event("Ignored $ip", $deviceid, 'interface', $if);
		continue;
	}

	// Attempt discovery of each IP only once per run.
	if (arp_discovery_is_cached($ip)) {
		echo(".");
		continue;
	}
	arp_discovery_add_cache($ip);

	// Log reverse DNS failures so the administrator can take action.
	$name = gethostbyaddr($ip);
	if ($name != $ip) {		// gethostbyaddr returns the original argument on failure
		echo("+");
		$names[] = $name;
		$ips[$name] = $ip;
	}
	else {
		echo("-");
		log_event("ARP discovery of $ip failed due to absent reverse DNS", $deviceid, 'interface', $if);
	}
}
echo("\n");

// Run device discovery on each of the devices we've detected so far.
foreach ($names as $name) {
	$remote_device_id = discover_new_device($name);
	if ($remote_device_id) {
		log_event("Device autodiscovered through ARP on $hostname", $remote_device_id, 'interface', $if);
	}
	else {
		log_event("ARP discovery of $name (" . $ips[$name] . ") failed - check ping and SNMP access", $deviceid, 'interface', $if);
	}
}

unset($names);
unset($ips);

?>

