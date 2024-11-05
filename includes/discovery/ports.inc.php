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

$port_stats = [];
$port_stats = snmpwalk_cache_oid($device, 'ifDescr', $port_stats, 'IF-MIB', null, $descrSnmpFlags);
$port_stats = snmpwalk_cache_oid($device, 'ifName', $port_stats, 'IF-MIB');
$port_stats = snmpwalk_cache_oid($device, 'ifAlias', $port_stats, 'IF-MIB');
$port_stats = snmpwalk_cache_oid($device, 'ifType', $port_stats, 'IF-MIB', null, $typeSnmpFlags);
$port_stats = snmpwalk_cache_oid($device, 'ifOperStatus', $port_stats, 'IF-MIB', null, $operStatusSnmpFlags);

// Get Trellix NSP ports
if ($device['os'] == 'mlos-nsp') {
    require base_path('includes/discovery/ports/mlos-nsp.inc.php');
}

//Get UFiber OLT ports
if ($device['os'] == 'edgeosolt') {
    require base_path('includes/discovery/ports/edgeosolt.inc.php');
}

//Get loop-telecom line card interfaces
if ($device['os'] == 'loop-telecom') {
    require base_path('includes/discovery/ports/loop-telecom.inc.php');
}

//Change Zynos ports from swp to 1/1
if ($device['os'] == 'zynos') {
    require base_path('includes/discovery/ports/zynos.inc.php');
}

// Get correct eth0 port status for AirFiber 5XHD devices
if ($device['os'] == 'airos-af-ltu') {
    require 'ports/airos-af-ltu.inc.php';
}

//Teleste Luminato ifOperStatus
if ($device['os'] == 'luminato') {
    require base_path('includes/discovery/ports/luminato.inc.php');
}

//Moxa Etherdevice portName mapping
if ($device['os'] == 'moxa-etherdevice') {
    require base_path('includes/discovery/ports/moxa-etherdevice.inc.php');
}

//Remove extra ports on Zhone slms devices
if ($device['os'] == 'slms') {
    require base_path('includes/discovery/ports/slms.inc.php');
}

//Cambium cnMatrix port description mapping
if ($device['os'] == 'cnmatrix') {
    require base_path('includes/discovery/ports/cnmatrix.inc.php');
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
    $port_association_mode = PortAssociationMode::getName($device['port_association_mode']);
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
    require base_path('includes/discovery/ports/brocade.inc.php');
}

//Shorten Ekinops Interfaces
if ($device['os'] == 'ekinops') {
    require base_path('includes/discovery/ports/ekinops.inc.php');
}

$default_port_group = Config::get('default_port_group');

// New interface detection
foreach ($port_stats as $ifIndex => $snmp_data) {
    $snmp_data['ifIndex'] = $ifIndex; // Store ifIndex in port entry
    $snmp_data['ifAlias'] = StringHelpers::inferEncoding($snmp_data['ifAlias'] ?? null);

    // Get port_id according to port_association_mode used for this device
    $port_id = get_port_id($ports_mapped, $snmp_data, $port_association_mode);

    if (is_port_valid($snmp_data, $device)) {
        port_fill_missing_and_trim($snmp_data, $device);

        if ($device['os'] == 'vmware-vcsa' && preg_match('/Device ([a-z0-9]+) at .*/', $snmp_data['ifDescr'], $matches)) {
            $snmp_data['ifName'] = $matches[1];
        }

        // Port newly discovered?
        if (! isset($ports_db[$port_id]) || ! is_array($ports_db[$port_id])) {
            $snmp_data['device_id'] = $device['device_id'];
            $port_id = dbInsert($snmp_data, 'ports');

            //default Port Group for new Ports defined?
            if (! empty($default_port_group)) {
                $port_group = PortGroup::find($default_port_group);
                if (isset($port_group)) {
                    $port_group->ports()->attach([$port_id]);
                }
            }

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
        if (isset($ports_db[$port_id]) && is_array($ports_db[$port_id])) {
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
