<?php

namespace LibreNMS\DB;

// Build SNMP Cache Array
use Illuminate\Support\Facades\DB;
use LibreNMS\Config;
use LibreNMS\Enum\PortAssociationMode;

$table = 'port_security';
$port_id_field = 'port_id';
$device_id_field = 'device_id';
$sticky_macs_field = 'cpsIfStickyEnable';
$max_macs_field = 'cpsIfMaxSecureMacAddr';

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
        $device_id = $device['device_id'];
        // Needs to be an existing port. Checking if it's in the ports table
        $where = [[$port_id_field, '=', $port_id], [$device_id_field, '=', $device_id]];
        $output = DB::table('ports')->where($where)->get();
        $port_info = json_decode(json_encode($output), true);
        // Only concerned with physical ports
        if ($port_info[0]['ifType'] == 'ethernetCsmacd') {
            // Checking if port already exists in port_security table. Update if yes, insert if not.
            $port_sec_info = DB::table($table)->select($port_id_field, $device_id_field)->get();
            $max_macs_value = $snmp_data['cpsIfMaxSecureMacAddr'];
            $sticky_macs_value = $snmp_data['cpsIfStickyEnable'];
            //echo '<pre>'; print_r($db_data); echo '</pre>';
            if ($port_sec_info) {
                echo 'Updating data';
                $update = [$sticky_macs_field => $sticky_macs_value, $max_macs_field => $max_macs_value];
                $output = DB::table($table)->where($port_id_field, $port_id)->update($update);
            } else {
                echo 'Inserting data';
                // $db_return = dbInsert($db_data, 'port_security');
                $insert_info = [$port_id_field => $port_id, $device_id_field => $device_id, $sticky_macs_field => $sticky_macs_value, $max_macs_field => $max_macs_value];
                $output = DB::table($table)->insert($insert_info);
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
