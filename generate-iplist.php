#!/usr/bin/env php
<?php

/**
 * LibreNMS
 *
 *   This file is part of LibreNMS
 *
 * @package    librenms
 * @subpackage cli
 * @author     LibreNMS Group <librenms-project@google.groups.com>
 * @copyright  (C) 2006 - 2012 Adam Armstrong (as Observium)
 * @copyright  (C) 2013 LibreNMS Group
 *
 */

include("includes/defaults.inc.php");
include("config.php");
include("includes/definitions.inc.php");
include("includes/functions.php");

$handle = fopen("ips.txt", "w");

foreach (dbFetchRows("SELECT * FROM `ipv4_networks`"))
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
      if (dbFetchCell("SELECT COUNT(ipv4_address_id) FROM `ipv4_addresses` WHERE `ipv4_address` = ?", array($ipdotted)) == '0' && match_network($config['nets'], $ipdotted))
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
