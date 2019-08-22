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
$unit_text     = 'v3 read operations';
$unitlen       = 10;
$bigdescrlen   = 15;
$smalldescrlen = 15;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 33;

$rrd_filename  = rrd_name($device['hostname'], array('app', 'nfs-server-proc3', $app['app_id']));

$array         = array(
                 'proc3_getattr' => array('descr' => 'Get attributes'),
                 'proc3_lookup' => array('descr' => 'Lookup'),
                 'proc3_access' => array('descr' => 'Access'),
                 'proc3_readlink' => array('descr' => 'Read link'),
                 'proc3_readdir' => array('descr' => 'Read dir'),
                 'proc3_readdirplus' => array('descr' => 'Read dir plus'),
                 'proc3_fsstat' => array('descr' => 'FS stat'),
                 'proc3_fsinfo' => array('descr' => 'FS info'),
                 'proc3_pathconf' => array('descr' => 'Pathconf'),
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
