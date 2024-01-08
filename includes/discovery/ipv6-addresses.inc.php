<?php

use LibreNMS\Config;
use LibreNMS\Exceptions\InvalidIpException;
use LibreNMS\Util\IPv6;

foreach (DeviceCache::getPrimary()->getVrfContexts() as $context_name) {
    $device['context_name'] = $context_name;

    if (file_exists(Config::get('install_dir') . "/includes/discovery/ipv6-addresses/{$device['os']}.inc.php")) {
        include Config::get('install_dir') . "/includes/discovery/ipv6-addresses/{$device['os']}.inc.php";
    } else {
        $oids = SnmpQuery::enumStrings()->abortOnFailure()
            ->walk(['IP-MIB::ipAddressIfIndex.ipv6', 'IP-MIB::ipAddressOrigin.ipv6', 'IP-MIB::ipAddressPrefix.ipv6'])
            ->table(4);
        foreach ($oids['ipv6'] ?? [] as $address => $data) {
            try {
                $ifIndex = $data['IP-MIB::ipAddressIfIndex'];
                $ipv6_address = IPv6::fromHexString($address)->uncompressed();
                $ipv6_origin = $data['IP-MIB::ipAddressOrigin'];
                preg_match('/(\d{1,3})]$/', $data['IP-MIB::ipAddressPrefix'], $prefix_match);
                $ipv6_prefixlen = $prefix_match[1] ?? 0;
                discover_process_ipv6($valid, $ifIndex, $ipv6_address, $ipv6_prefixlen, $ipv6_origin, $device['context_name']);
            } catch (InvalidIpException $e) {
                d_echo("Failed to decode ipv6: $address");
            }
        }
    }

    if (empty($oids) || empty($valid)) {
        $oids = snmp_walk($device, 'ipv6AddrPfxLength', ['-OsqnU', '-Ln'], 'IPV6-MIB');
        $oids = str_replace('.1.3.6.1.2.1.55.1.8.1.2.', '', $oids);
        $oids = str_replace('"', '', $oids);
        $oids = trim($oids);

        foreach (explode("\n", $oids) as $data) {
            if ($data) {
                $data = trim($data);
                [$if_ipv6addr,$ipv6_prefixlen] = explode(' ', $data);
                [$ifIndex,$ipv6addr] = explode('.', $if_ipv6addr, 2);
                $ipv6_address = snmp2ipv6($ipv6addr);
                $ipv6_origin = snmp_get($device, "IPV6-MIB::ipv6AddrType.$if_ipv6addr", '-Ovq', 'IPV6-MIB');
                discover_process_ipv6($valid, $ifIndex, $ipv6_address, $ipv6_prefixlen, $ipv6_origin, $device['context_name']);
            } //end if
        } //end foreach
    } //end if

    $sql = 'SELECT `ipv6_addresses`.*, `ports`.`device_id`, `ports`.`ifIndex` FROM `ipv6_addresses`';
    $sql .= ' LEFT JOIN `ports` ON `ipv6_addresses`.`port_id` = `ports`.`port_id`';
    $sql .= ' WHERE `ports`.device_id = ? OR `ports`.`device_id` IS NULL';
    foreach (dbFetchRows($sql, [$device['device_id']]) as $row) {
        $full_address = $row['ipv6_address'] . '/' . $row['ipv6_prefixlen'];
        $port_id = $row['port_id'];
        $valid_address = $full_address . '-' . $port_id;
        if (! $valid['ipv6'][$valid_address]) {
            echo '-';
            $query = dbDelete('ipv6_addresses', '`ipv6_address_id` = ?', [$row['ipv6_address_id']]);
            if (! dbFetchCell('SELECT COUNT(*) FROM `ipv6_addresses` WHERE `ipv6_network_id` = ?', [$row['ipv6_network_id']])) {
                $query = dbDelete('ipv6_networks', '`ipv6_network_id` = ?', [$row['ipv6_network_id']]);
            }
        }
    }

    echo "\n";
    unset($device['context_name']);
}
unset($vrfs_c);
