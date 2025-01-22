<?php

/**
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */

use App\Facades\PortCache;
use LibreNMS\Config;
use LibreNMS\Util\Url;

$init_modules = ['web', 'auth'];
require realpath(__DIR__ . '/..') . '/includes/init.php';

if (is_numeric($_GET['id']) && (Config::get('allow_unauth_graphs') || port_permitted($_GET['id']))) {
    $port = cleanPort(PortCache::get($_GET['id'])->load('device'));
    $title = Url::deviceLink($port->device) . ' :: Port  ' . Url::portLink($port);
    $auth = true;

    $in = SnmpQuery::get('IF-MIB::ifHCInOctets.' . $port->ifIndex)->value();
    if (empty($in)) {
        $in = SnmpQuery::get('IF-MIB::ifInOctets.' . $port->ifIndex)->value();
    }

    $out = SnmpQuery::get('IF-MIB::ifHCOutOctets.' . $port->ifIndex)->value();
    if (empty($out)) {
        $out = SnmpQuery::get('IF-MIB::ifOutOctets.' . $port->ifIndex)->value();
    }

    $time = microtime(true);

    printf("%lf|%s|%s\n", $time, $in, $out);
}
