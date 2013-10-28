<?php

/**
 * LibreNMS
 *
 *   This file is part of LibreNMS
 *
 * @package    librenms
 * @subpackage webinterface
 * @author     The LibreNMS Contributors <librenms-project@google.groups.com>
 * @copyright  (C) 2006 - 2012 Adam Armstrong (as Observium)
 * @copyright  (C) 2013 LibreNMS Contributors
 *
 */

// FIXME - fewer includes!

include_once("../includes/defaults.inc.php");
include_once("../config.php");
include_once("../includes/definitions.inc.php");
include_once("../includes/common.php");
include_once("../includes/dbFacile.php");
include_once("../includes/rewrites.php");
include_once("includes/functions.inc.php");
include_once("includes/authenticate.inc.php");

include_once("../includes/snmp.inc.php");

if (is_numeric($_GET['id']) && ($config['allow_unauth_graphs'] || port_permitted($_GET['id'])))
{
  $port   = get_port_by_id($_GET['id']);
  $device = device_by_id_cache($port['device_id']);
  $title  = generate_device_link($device);
  $title .= " :: Port  ".generate_port_link($port);
  $auth   = TRUE;
}

$in  = snmp_get($device, "ifInOctets.".$port['ifIndex'], "-OUqnv", "IF-MIB");
$out = snmp_get($device, "ifOutOctets.".$port['ifIndex'], "-OUqnv", "IF-MIB");

$time = time();

printf("%lf|%s|%s\n", time(), $in, $out);

?>
