<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       https://www.librenms.org
 * @copyright  2020 LibreNMS
 * @author     Cercel Valentin <crc@nuamchefazi.ro>
*/

require 'includes/html/graphs/common.inc.php';

$scale_min = 0;
$colours = 'mixed';
$unit_text = 'E-mail(s)';
$unitlen = 10;
$bigdescrlen = 20;
$smalldescrlen = 20;
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 33;
$rrd_filename = Rrd::name($device['hostname'], ['app', $app['app_type'], $app['app_id']]);

$array = [
    'received' => ['descr' => 'Received', 'colour' => '75a832'],
    'delivered' => ['descr' => 'Delivered', 'colour' => '00d644'],
    'forwarded' => ['descr' => 'Forwarded', 'colour' => 'ccff99'],
    'deferred' => ['descr' => 'Deferred', 'colour' => 'ffcc66'],
    'bounced' => ['descr' => 'Bounced', 'colour' => 'cc6600'],
    'rejected' => ['descr' => 'Rejected', 'colour' => 'cc0000'],
    'held' => ['descr' => 'Held', 'colour' => '3366cc'],
    'discarded' => ['descr' => 'Discarded', 'colour' => '1a1a1a'],
];

$i = 0;

if (Rrd::checkRrdExists($rrd_filename)) {
    foreach ($array as $ds => $var) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr'] = $var['descr'];
        $rrd_list[$i]['ds'] = $ds;
        $rrd_list[$i]['colour'] = $var['colour'];
        $i++;
    }
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_v3_multiline.inc.php';
