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
 * @copyright  (C) 2013 LibreNMS Contributors
 *
 */

chdir(dirname($argv[0]));

include("includes/defaults.inc.php");
include("config.php");
include("includes/definitions.inc.php");
include("includes/functions.php");

# Remove a host and all related data from the system

if ($argv[1])
{
  $host = strtolower($argv[1]);
  $id = getidbyname($host);
  if ($id)
  {
    echo(delete_device($id));
    echo("Removed $host\n");
  } else {
    echo("Host doesn't exist!\n");
  }
} else {
  echo("Host Removal Tool\nUsage: ./delhost.php <hostname>\n");
}

?>
