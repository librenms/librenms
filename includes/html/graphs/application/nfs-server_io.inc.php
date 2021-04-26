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
$unit_text = 'Operations';
$unitlen = 10;
$bigdescrlen = 15;
$smalldescrlen = 15;
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 33;

$rrd_filename = Rrd::name($device['hostname'], ['app', 'nfs-server-default', $app['app_id']]);

$array = [
    'io_read' => ['descr' => 'read', 'colour' => '2B9220'],
    'io_write' => ['descr' => 'write', 'colour' => 'B0262D'],
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

/*
This would create a graph with reads above and write belows;
I can't find out how to adapt the legend. If you wish to swap graphs,
uncomment all the above untill <?php and uncomment below this note

$rrd_filename  = rrd_name($device['hostname'], array('app', 'nfs-server-default', $app['app_id']));

$ds_in  = 'io_read';
$ds_out = 'io_write';

require 'includes/html/graphs/generic_data.inc.php';

*/
