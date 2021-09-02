#!/usr/bin/env php
<?php

/*
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @package    LibreNMS
 * @subpackage discovery
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */

$init_modules = [];
require realpath(__DIR__ . '/..') . '/includes/init.php';

include_once 'Net/IPv4.php';

$handle = fopen('ips.txt', 'w');

foreach (dbFetchRows('SELECT * FROM `ipv4_networks`') as $data) {
    $cidr = $data['ipv4_network'];
    [$network, $bits] = explode('/', $cidr);
    if ($bits != '32' && $bits != '32' && $bits > '22') {
        $addr = Net_IPv4::parseAddress($cidr);
        $broadcast = $addr->broadcast;
        $ip = ip2long($network) + 1;
        $end = ip2long($broadcast);
        while ($ip < $end) {
            $ipdotted = long2ip($ip);
            if (dbFetchCell('SELECT COUNT(ipv4_address_id) FROM `ipv4_addresses` WHERE `ipv4_address` = ?', [$ipdotted]) == '0' && match_network(\LibreNMS\Config::get('nets'), $ipdotted)) {
                fputs($handle, $ipdotted . "\n");
            }

            $ip++;
        }
    }
}

fclose($handle);

shell_exec(\LibreNMS\Config::get('fping') . ' -t 100 -f ips.txt > ips-scanned.txt');
