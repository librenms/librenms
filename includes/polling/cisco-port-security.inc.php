<?php

// Build SNMP Cache Array
use App\Models\PortGroup;
use LibreNMS\Config;
use LibreNMS\Enum\PortAssociationMode;
use LibreNMS\Util\StringHelpers;

$descrSnmpFlags = '-OQUs';
$typeSnmpFlags = '-OQUs';
$operStatusSnmpFlags = '-OQUs';
if ($device['os'] == 'bintec-beip-plus') {
    $descrSnmpFlags = ['-OQUs', '-Cc'];
    $typeSnmpFlags = ['-OQUs', '-Cc'];
    $operStatusSnmpFlags = ['-OQUs', '-Cc'];
}

if ($device['os'] == 'ios' || $device['os'] == 'iosxe') {
    $port_stats = [];
    $port_stats = snmpwalk_cache_oid($device, 'cpsIfStickyEnable', $port_stats, 'CISCO-PORT-SECURITY-MIB');
    $port_stats = snmpwalk_cache_oid($device, 'cpsIfMaxSecureMacAddr', $port_stats, 'CISCO-PORT-SECURITY-MIB');

    // End Building SNMP Cache Array
    d_echo($port_stats);

    // By default libreNMS uses the ifIndex to associate ports on devices with ports discoverd/polled
    // before and stored in the database. On Linux boxes this is a problem as ifIndexes may be
    // unstable between reboots or (re)configuration of tunnel interfaces (think: GRE/OpenVPN/Tinc/...)
    // The port association configuration allows to choose between association via ifIndex, ifName,
    // or maybe other means in the future. The default port association mode still is ifIndex for
    // compatibility reasons.
    $port_association_mode = Config::get('default_port_association_mode');
    if ($device['port_association_mode']) {
        $port_association_mode = PortAssociationMode::getName($device['port_association_mode']);
    }

    // Build array of ports in the database and an ifIndex/ifName -> port_id map
    $ports_mapped = get_ports_mapped($device['device_id']);
    $ports_db = $ports_mapped['ports'];

    $default_port_group = Config::get('default_port_group');

    // Looping through all of the ports
    foreach ($port_stats as $ifIndex => $snmp_data) {
        $snmp_data['ifIndex'] = $ifIndex; // Store ifIndex in port entry
        // Get port_id according to port_association_mode used for this device
        $port_id = get_port_id($ports_mapped, $snmp_data, $port_association_mode);
	// Needs to be an existing port. Checking if it's in the ports table
	$port_info = dbFetchRow('SELECT * FROM `ports` WHERE `device_id` = ? AND `port_id` = ?', [$device['device_id'], $port_id]);
	// Only concerned with physical ports
	if ($port_info['ifType'] == 'ethernetCsmacd') {
	    // Checking if port already exists in port_security table. Update if yes, insert if not.
	    $port_sec_info = dbFetchRow('SELECT * FROM `port_security` WHERE `device_id` = ? AND `port_id` = ?', [$device['device_id'], $port_id]);
	    $db_data['port_id'] = $port_id;
            $db_data['device_id'] = $port_info['device_id'];
            $db_data['cpsIfMaxSecureMacAddr'] = $snmp_data['cpsIfMaxSecureMacAddr'];
	    $db_data['cpsIfStickyEnable'] = $snmp_data['cpsIfStickyEnable'];
            //echo '<pre>'; print_r($db_data); echo '</pre>';
	    if ($port_sec_info) {
		echo 'Updating data';
		dbUpdate($db_data, 'port_security', '`port_id` = ?', [$port_id]);
	    }
	    else {
		echo 'Inserting data';
		$db_return = dbInsert($db_data, 'port_security');
	    }
	}
    }//end foreach

    unset(
        $ports_mapped,
        $port
    );

    echo "\n";

    // Clear Variables Here
    unset($port_stats);
    unset($ports_db);
}