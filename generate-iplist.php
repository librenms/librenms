#!/usr/bin/env php
<?php

include("includes/defaults.inc.php");
include("config.php");
include("includes/functions.php");

$handle = fopen("ips.txt", "w");

$query = mysql_query("SELECT * FROM `ipv4_networks`");
while ($data = mysql_fetch_array($query))
{
    $cidr = $data['ipv4_network'];
    list ($network, $bits) = explode("/", $cidr);
    if ($bits != '32' && $bits != '32' && $bits > '22')
    {
        $addr = Net_IPv4::parseAddress($cidr);
        $broadcast = $addr->broadcast;
        $ip = ip2long($network) + '1';
        $end = ip2long($broadcast);
        while ($ip < $end)
        {
            $ipdotted = long2ip($ip);
            if (mysql_result(mysql_query("SELECT count(ipv4_address_id) FROM ipv4_addresses WHERE ipv4_address = '$ipdotted'"),0) == '0' && match_network($config['nets'], $ipdotted))
            {
                fputs($handle, $ipdotted . "\n");
            }
            $ip++;
        }
    }
}

fclose($handle);

shell_exec("fping -t 100 -f ips.txt > ips-scanned.txt");

?>
