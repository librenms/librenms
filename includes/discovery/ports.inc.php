<?php

// Build SNMP Cache Array
$port_stats = array();
$port_stats = snmpwalk_cache_oid($device, 'ifDescr', $port_stats, 'IF-MIB');
$port_stats = snmpwalk_cache_oid($device, 'ifName', $port_stats, 'IF-MIB');
$port_stats = snmpwalk_cache_oid($device, 'ifAlias', $port_stats, 'IF-MIB');
$port_stats = snmpwalk_cache_oid($device, 'ifType', $port_stats, 'IF-MIB');

// End Building SNMP Cache Array
d_echo($port_stats);


// By default libreNMS uses the ifIndex to associate ports on devices with ports discoverd/polled
// before and stored in the database. On Linux boxes this is a problem as ifIndexes may be
// unstable between reboots or (re)configuration of tunnel interfaces (think: GRE/OpenVPN/Tinc/...)
// The port association configuration allows to choose between association via ifIndex, ifName,
// or maybe other means in the future. The default port association mode still is ifIndex for
// compatibility reasons.
$port_association_mode = $config['default_port_association_mode'];
if ($device['port_association_mode']) {
    $port_association_mode = get_port_assoc_mode_name($device['port_association_mode']);
}

// Build array of ports in the database and an ifIndex/ifName -> port_id map
$ports_mapped = get_ports_mapped($device['device_id']);
$ports_db = $ports_mapped['ports'];

//
// Rename any old RRD files still named after the previous ifIndex based naming schema.
foreach ($ports_mapped['maps']['ifIndex'] as $ifIndex => $port_id) {
    foreach (array ('', '-adsl', '-dot3') as $suffix) {
        $old_rrd_name = "port-$ifIndex$suffix.rrd";
        $new_rrd_name = getPortRrdName($port_id, ltrim($suffix, '-'));

        rrd_file_rename($device, $old_rrd_name, $new_rrd_name);
    }
}


// New interface detection
foreach ($port_stats as $ifIndex => $port) {
    // Store ifIndex in port entry and prefetch ifName as we'll need it multiple times
    $port['ifIndex'] = $ifIndex;
    $ifName = $port['ifName'];
    $ifAlias = $port['ifAlias'];
    $ifDescr = $port['ifDescr'];

    // Get port_id according to port_association_mode used for this device
    $port_id = get_port_id($ports_mapped, $port, $port_association_mode);
    if (is_port_valid($port, $device)) {
        // Port newly discovered?
        if (! is_array($ports_db[$port_id])) {
            $port_id         = dbInsert(array('device_id' => $device['device_id'], 'ifIndex' => $ifIndex, 'ifName' => $ifName, 'ifAlias' => $ifAlias, 'ifDescr' => $ifDescr), 'ports');
            $ports[$port_id] = dbFetchRow('SELECT * FROM `ports` WHERE `device_id` = ? AND `port_id` = ?', array($device['device_id'], $port_id));
            echo 'Adding: '.$ifName.'('.$ifIndex.')('.$port_id.')';
        } // Port re-discovered after previous deletion?
        elseif ($ports_db[$port_id]['deleted'] == '1') {
            dbUpdate(array('deleted' => '0'), 'ports', '`port_id` = ?', array($port_id));
            $ports_db[$port_id]['deleted'] = '0';
            echo 'U';
        } else {
            echo '.';
        }

        // We've seen it. Remove it from the cache.
        unset($ports_l[$ifIndex]);
    } // Port vanished (mark as deleted)
    else {
        if (is_array($ports_db[$port_id])) {
            if ($ports_db[$port_id]['deleted'] != '1') {
                dbUpdate(array('deleted' => '1'), 'ports', "`port_id` = ?, `ifName` => '?', `ifAlias` => '?', `ifDescr` => '?'", array($port_id, $ifName, $ifAlias, $ifDescr));
                $ports_db[$port_id]['deleted'] = '1';
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

// End New interface detection
// Interface Deletion
// If it's in our $ports_l list, that means it's not been seen. Mark it deleted.
foreach ($ports_l as $ifIndex => $port_id) {
    if ($ports_db[$ifIndex]['deleted'] == '0') {
        dbUpdate(array('deleted' => '1'), 'ports', '`port_id` = ?', array($port_id));
        echo '-'.$ifIndex;
    }
}

// End interface deletion
echo "\n";

// Clear Variables Here
unset($port_stats);
unset($ports_db);
