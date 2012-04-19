#!/usr/bin/env php
<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2011, Observium Developers - http://www.observium.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See COPYING for more details.
 *
 * @package    observium
 * @subpackage cli
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 * @license    http://gnu.org/copyleft/gpl.html GNU GPL
 *
 */

include("includes/defaults.inc.php");
include("config.php");
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
