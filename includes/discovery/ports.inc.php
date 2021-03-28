<?php

// Build SNMP Cache Array
use LibreNMS\Config;

$port_stats = [];
$port_stats = snmpwalk_cache_oid($device, 'ifDescr', $port_stats, 'IF-MIB');
$port_stats = snmpwalk_cache_oid($device, 'ifName', $port_stats, 'IF-MIB');
$port_stats = snmpwalk_cache_oid($device, 'ifAlias', $port_stats, 'IF-MIB');
$port_stats = snmpwalk_cache_oid($device, 'ifType', $port_stats, 'IF-MIB');
$port_stats = snmpwalk_cache_oid($device, 'ifOperStatus', $port_stats, 'IF-MIB');

// Get correct eth0 port status for AirFiber 5XHD devices
if ($device['os'] == 'airos-af-ltu') {
    require 'ports/airos-af-ltu.inc.php';
}

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
    $port_association_mode = get_port_assoc_mode_name($device['port_association_mode']);
}

// Build array of ports in the database and an ifIndex/ifName -> port_id map
$ports_mapped = get_ports_mapped($device['device_id']);
$ports_db = $ports_mapped['ports'];

//
// Rename any old RRD files still named after the previous ifIndex based naming schema.
foreach ($ports_mapped['maps']['ifIndex'] as $ifIndex => $port_id) {
    foreach (['', '-adsl', '-dot3'] as $suffix) {
        $old_rrd_name = "port-$ifIndex$suffix.rrd";
        $new_rrd_name = \Rrd::portName($port_id, ltrim($suffix, '-'));

        \Rrd::renameFile($device, $old_rrd_name, $new_rrd_name);
    }
}

// Fill ifAlias for fibrechannel ports
if ($device['os'] == 'fabos') {
    require_once 'ports/brocade.inc.php';
}

//Shorten Ekinops Interfaces
if ($device['os'] == 'ekinops') {
    require_once 'ports/ekinops.inc.php';
}

// New interface detection
foreach ($port_stats as $ifIndex => $snmp_data) {
    $snmp_data['ifIndex'] = $ifIndex; // Store ifIndex in port entry

    // Get port_id according to port_association_mode used for this device
    $port_id = get_port_id($ports_mapped, $snmp_data, $port_association_mode);

    if (is_port_valid($snmp_data, $device)) {
        port_fill_missing($snmp_data, $device);

        // Port newly discovered?
        if (! is_array($ports_db[$port_id])) {
            $snmp_data['device_id'] = $device['device_id'];
            $port_id = dbInsert($snmp_data, 'ports');
            $ports[$port_id] = dbFetchRow('SELECT * FROM `ports` WHERE `device_id` = ? AND `port_id` = ?', [$device['device_id'], $port_id]);
            echo 'Adding: ' . $snmp_data['ifName'] . '(' . $ifIndex . ')(' . $port_id . ')';
        } elseif ($ports_db[$port_id]['deleted'] == 1) {
            // Port re-discovered after previous deletion?
            $snmp_data['deleted'] = 0;
            dbUpdate($snmp_data, 'ports', '`port_id` = ?', [$port_id]);
            $ports_db[$port_id]['deleted'] = 0;
            echo 'U';
        } else { // port is existing, let's update it with some data we have collected here
            dbUpdate($snmp_data, 'ports', '`port_id` = ?', [$port_id]);
            echo '.';
        }
    } else {
        // Port vanished (mark as deleted)
        if (is_array($ports_db[$port_id])) {
            if ($ports_db[$port_id]['deleted'] != 1) {
                dbUpdate(['deleted' => 1], 'ports', '`port_id` = ?', [$port_id]);
                $ports_db[$port_id]['deleted'] = 1;
                echo '-';
            }
        }

        echo 'X';
    }//end if
}//end foreach

unset(
    $ports_mapped,
    $port
);

echo "\n";

// Clear Variables Here
unset($port_stats);
unset($ports_db);
