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
$unit_text = 'v3 read operations';
$unitlen = 10;
$bigdescrlen = 15;
$smalldescrlen = 15;
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 33;

$rrd_filename = Rrd::name($device['hostname'], ['app', 'nfs-server-proc3', $app['app_id']]);

$array = [
    'proc3_getattr' => ['descr' => 'Get attributes'],
    'proc3_lookup' => ['descr' => 'Lookup'],
    'proc3_access' => ['descr' => 'Access'],
    'proc3_readlink' => ['descr' => 'Read link'],
    'proc3_readdir' => ['descr' => 'Read dir'],
    'proc3_readdirplus' => ['descr' => 'Read dir plus'],
    'proc3_fsstat' => ['descr' => 'FS stat'],
    'proc3_fsinfo' => ['descr' => 'FS info'],
    'proc3_pathconf' => ['descr' => 'Pathconf'],
];

$i = 0;

if (Rrd::checkRrdExists($rrd_filename)) {
    foreach ($array as $ds => $var) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr'] = $var['descr'];
        $rrd_list[$i]['ds'] = $ds;
        $rrd_list[$i]['colour'] = \LibreNMS\Config::get("graph_colours.$colours.$i");
        $i++;
    }
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_v3_multiline.inc.php';
