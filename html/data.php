<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webinterface
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */

// FIXME - fewer includes!
require_once '../includes/defaults.inc.php';
require_once '../config.php';
require_once '../includes/definitions.inc.php';
require_once '../includes/common.php';
require_once '../includes/dbFacile.php';
require_once '../includes/rewrites.php';
require_once 'includes/functions.inc.php';
require_once 'includes/authenticate.inc.php';

require_once '../includes/snmp.inc.php';

if (is_numeric($_GET['id']) && ($config['allow_unauth_graphs'] || port_permitted($_GET['id']))) {
    $port   = get_port_by_id($_GET['id']);
    $device = device_by_id_cache($port['device_id']);
    $title  = generate_device_link($device);
    $title .= ' :: Port  '.generate_port_link($port);
    $auth   = true;
}

$in = snmp_get($device, 'ifHCInOctets.'.$port['ifIndex'], '-OUqnv', 'IF-MIB');
if (empty($in)) {
    $in  = snmp_get($device, 'ifInOctets.'.$port['ifIndex'], '-OUqnv', 'IF-MIB');
}

$out = snmp_get($device, 'ifHCOutOctets.'.$port['ifIndex'], '-OUqnv', 'IF-MIB');
if (empty($out)) {
    $out = snmp_get($device, 'ifOutOctets.'.$port['ifIndex'], '-OUqnv', 'IF-MIB');
}

$time = time();

printf("%lf|%s|%s\n", time(), $in, $out);
