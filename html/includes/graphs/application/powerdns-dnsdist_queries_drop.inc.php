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
 * @link       http://librenms.org
 * @copyright  2017 LibreNMS
 * @author     Cercel Valentin <crc@nuamchefazi.ro>
*/

require 'includes/html/graphs/common.inc.php';
$scale_min     = 0;
$colours       = 'mixed';
$unit_text     = 'Queries drop';
$unitlen       = 16;
$bigdescrlen   = 25;
$smalldescrlen = 25;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 33;
$rrd_filename = rrd_name($device['hostname'], array('app', $app['app_type'], $app['app_id']));

$array = array(
    'queries_drop_no_policy' => array('descr' => 'No server', 'colour' => 'aa0635',),
    'queries_drop_nc' => array('descr' => 'Non-compliant', 'colour' => 'cc6985',),
    'queries_drop_nc_answer' => array('descr' => 'Non-compliant answer', 'colour' => '2d2d2d',),
    'queries_acl_drop' => array('descr' => 'ACL', 'colour' => '008442',),
    'queries_failure' => array('descr' => 'Failure', 'colour' => 'e55b38',),
    'queries_serv_fail' => array('descr' => 'Servfail', 'colour' => '9a3e3e',),
);

$i = 0;

if (rrdtool_check_rrd_exists($rrd_filename)) {
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

require 'includes/html/graphs/generic_v3_multiline_float.inc.php';
