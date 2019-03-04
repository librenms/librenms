<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       http://librenms.org
 * @copyright  2019 LibreNMS
 * @author     LibreNMS Contributors
*/

global $config;

$total = true;

if (isset($total) && $total === true) {
    $graphs = [
        'shoutcast_multi_bits' => 'Traffic Statistics - Total of all Shoutcast servers',
        'shoutcast_multi_stats' => 'Shoutcast Statistics - Total of all Shoutcast servers',
    ];
    include "app.bootstrap.inc.php";
}

$files = glob(rrd_name($device['hostname'], array('app', 'shoutcast', $app['app_id']), '*.rrd'));
foreach ($files as $file) {
    $pieces = explode('-', basename($file, '.rrd'));
    $hostname = end($pieces);
    list($host, $port) = explode('_', $hostname, 2);
    $graphs = [
        'shoutcast_bits' => 'Traffic Statistics - ' . $host . ' (Port: ' . $port . ')',
        'shoutcast_stats' => 'Shoutcast Statistics - ' . $host . ' (Port: ' . $port . ')',
    ];
    include "app.shoutcast.boostrap.inc.php";
}
