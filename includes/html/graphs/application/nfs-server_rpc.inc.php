<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage nfs-server
 * @link       https://www.librenms.org
 * @copyright  2017 LibreNMS
 * @author     SvennD <svennd@svennd.be>
*/

require 'includes/html/graphs/common.inc.php';
$scale_min = 0;
$colours = 'mixed';
$unit_text = 'RPC Stats';
$unitlen = 15;
$bigdescrlen = 15;
$smalldescrlen = 15;
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 33;

$rrd_filename = Rrd::name($device['hostname'], ['app', 'nfs-server-default', $app['app_id']]);

$array = [
    'rpc_calls' => ['descr' => 'calls', 'colour' => '2C8437'], // green : good
    //'rpc_badcalls' => array('descr' => 'bad calls','colour' => '600604',), # this is a sum of nbadfmt, badauth and badclnt
    'rpc_badfmt' => ['descr' => 'bad fmt', 'colour' => 'E6A4A5'], // pink
    'rpc_badauth' => ['descr' => 'bad auth', 'colour' => 'B2C8D9'], // blue
    'rpc_badclnt' => ['descr' => 'bad clnt', 'colour' => 'BEA37A'], // brown
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
