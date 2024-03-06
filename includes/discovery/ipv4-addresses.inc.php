<?php
/**
 * ipv4-addresses.inc.php
 *
 * IPv4 address discovery module
 *
 *
 * @link       https://www.librenms.org
 *
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

use App\Models\Eventlog;
use App\Models\Ipv4Address;
use App\Models\Ipv4Network;
use LibreNMS\Config;
use LibreNMS\Enum\Severity;

foreach (DeviceCache::getPrimary()->getVrfContexts() as $context_name) {
    $device['context_name'] = $context_name;

    if (file_exists(Config::get('install_dir') . "/includes/discovery/ipv4-addresses/{$device['os']}.inc.php")) {
        include Config::get('install_dir') . "/includes/discovery/ipv4-addresses/{$device['os']}.inc.php";
    } else {
        unset($valid_v4);
        $oids = SnmpQuery::hideMib()->walk('IP-MIB::ipAdEntIfIndex')->table(1);
        foreach ($oids as $ipv4_address => $indexArray) {
            $ifIndex = intval($indexArray['ipAdEntIfIndex']);
            $mask = SnmpQuery::get('IP-MIB::ipAdEntNetMask.' . $ipv4_address)->value();
            discover_process_ipv4($valid_v4, $device, $ifIndex, $ipv4_address, $mask, $context_name);
        }
    } // if [custom / standard]

    $fromDb = Ipv4Address::where('ports.device_id', $device['device_id'])->orWhere('ports.device_id', null)
        ->select('ipv4_address_id', 'ipv4_address', 'ipv4_prefixlen', 'ipv4_network_id', 'ports.device_id', 'ports.ifIndex')
        ->leftJoin('ports', 'ipv4_addresses.port_id', '=', 'ports.port_id')
        ->get()->toArray();

    foreach ($fromDb as $row) {
        $full_address = $row['ipv4_address'] . '/' . $row['ipv4_prefixlen'] . '|' . $row['ifIndex'];
        if (empty($valid_v4[$full_address])) {
            Ipv4Address::where('ipv4_address_id', $row['ipv4_address_id'])->delete();
            Eventlog::log('IPv4 address: ' . $row['ipv4_address'] . '/' . $row['ipv4_prefixlen'] . ' deleted', $device['device_id'], 'ipv4', Severity::Warning);
            echo 'A-';
            if (! Ipv4Address::where('ipv4_network_id', $row['ipv4_network_id'])->count()) {
                Ipv4Network::where('ipv4_network_id', $row['ipv4_network_id'])->delete();
                echo 'N-';
            }
        }
    }

    echo PHP_EOL;
    unset($device['context_name']);
}
unset($valid_v4);
unset($vrfs_c);
