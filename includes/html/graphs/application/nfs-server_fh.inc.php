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
 * @link       http://librenms.org
 * @copyright  2017 LibreNMS
 * @author     SvennD <svennd@svennd.be>
*/

require 'includes/html/graphs/common.inc.php';
$scale_min     = 0;
$colours       = 'mixed';
$unit_text     = 'File Handle Stats';
$unitlen       = 15;
$bigdescrlen   = 15;
$smalldescrlen = 15;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 33;

$rrd_filename  = rrd_name($device['hostname'], array('app', 'nfs-server-default', $app['app_id']));

$array = array(
    'fh_lookup' => array('descr' => 'fh_lookup'),
    'fh_anon' => array('descr' => 'fh_anon'),
    'fh_ncachedir' => array('descr' => 'fh_ncachedir'),
    'fh_ncachenondir' => array('descr' => 'fh_ncachenondir'),
    'fh_stale' => array('descr' => 'fh_stale'), // only var that should show something
);

$i = 0;

if (rrdtool_check_rrd_exists($rrd_filename)) {
    foreach ($array as $ds => $var) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr']    = $var['descr'];
        $rrd_list[$i]['ds']       = $ds;
        $rrd_list[$i]['colour'] = \LibreNMS\Config::get("graph_colours.$colours.$i");
        $i++;
    }
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_v3_multiline.inc.php';
