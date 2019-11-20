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
$unit_text     = 'NFS v4 Operations';
$unitlen       = 10;
$bigdescrlen   = 15;
$smalldescrlen = 15;
$dostack       = 0;
$printtotal    = 0;
$addarea       = 1;
$transparency  = 33;

$rrd_filename  = rrd_name($device['hostname'], array('app', 'nfs-server-proc4ops', $app['app_id']));

$array         = array(
                        // 'v4_op0-unused' => array('descr' => 'v4_op0-unused'),
                        // 'v4_op1-unused' => array('descr' => 'v4_op1-unused'),
                        // 'v4_op2-future' => array('descr' => 'v4_op2-future'),
                        'v4_access' => array('descr' => 'v4_access'),
                        'v4_close' => array('descr' => 'v4_close'),
                        'v4_commit' => array('descr' => 'v4_commit'),
                        'v4_create' => array('descr' => 'v4_create'),
                        'v4_delegpurge' => array('descr' => 'v4_delegpurge'),
                        'v4_delegreturn' => array('descr' => 'v4_delegreturn'),
                        'v4_getattr' => array('descr' => 'v4_getattr'),
                        'v4_getfh' => array('descr' => 'v4_getfh'),
                        'v4_link' => array('descr' => 'v4_link'),
                        'v4_lock' => array('descr' => 'v4_lock'),
                        'v4_lockt' => array('descr' => 'v4_lockt'),
                        'v4_locku' => array('descr' => 'v4_locku'),
                        'v4_lookup' => array('descr' => 'v4_lookup'),
                        'v4_lookup_root' => array('descr' => 'v4_lookup_root'),
                        'v4_nverify' => array('descr' => 'v4_nverify'),
                        'v4_open' => array('descr' => 'v4_open'),
                        'v4_openattr' => array('descr' => 'v4_openattr'),
                        'v4_open_conf' => array('descr' => 'v4_open_conf'),
                        'v4_open_dgrd' => array('descr' => 'v4_open_dgrd'),
                        'v4_putfh' => array('descr' => 'v4_putfh'),
                        'v4_putpubfh' => array('descr' => 'v4_putpubfh'),
                        'v4_putrootfh' => array('descr' => 'v4_putrootfh'),
                        'v4_read' => array('descr' => 'v4_read'),
                        'v4_readdir' => array('descr' => 'v4_readdir'),
                        'v4_readlink' => array('descr' => 'v4_readlink'),
                        'v4_remove' => array('descr' => 'v4_remove'),
                        'v4_rename' => array('descr' => 'v4_rename'),
                        'v4_renew' => array('descr' => 'v4_renew'),
                        'v4_restorefh' => array('descr' => 'v4_restorefh'),
                        'v4_savefh' => array('descr' => 'v4_savefh'),
                        'v4_secinfo' => array('descr' => 'v4_secinfo'),
                        'v4_setattr' => array('descr' => 'v4_setattr'),
                        'v4_setcltid' => array('descr' => 'v4_setcltid'),
                        'v4_setcltidconf' => array('descr' => 'v4_setcltidconf'),
                        'v4_verify' => array('descr' => 'v4_verify'),
                        'v4_write' => array('descr' => 'v4_write'),
                        'v4_rellockowner' => array('descr' => 'v4_rellockowner'),
                        'v4_bc_ctl' => array('descr' => 'v4_bc_ctl'),
                        'v4_bind_conn' => array('descr' => 'v4_bind_conn'),
                        'v4_exchange_id' => array('descr' => 'v4_exchange_id'),
                        'v4_create_ses' => array('descr' => 'v4_create_ses'),
                        'v4_destroy_ses' => array('descr' => 'v4_destroy_ses'),
                        'v4_free_stateid' => array('descr' => 'v4_free_stateid'),
                        'v4_getdirdeleg' => array('descr' => 'v4_getdirdeleg'),
                        'v4_getdevinfo' => array('descr' => 'v4_getdevinfo'),
                        'v4_getdevlist' => array('descr' => 'v4_getdevlist'),
                        'v4_layoutcommit' => array('descr' => 'v4_layoutcommit'),
                        'v4_layoutget' => array('descr' => 'v4_layoutget'),
                        'v4_layoutreturn' => array('descr' => 'v4_layoutreturn'),
                        'v4_secinfononam' => array('descr' => 'v4_secinfononam'),
                        'v4_sequence' => array('descr' => 'v4_sequence'),
                        'v4_set_ssv' => array('descr' => 'v4_set_ssv'),
                        'v4_test_stateid' => array('descr' => 'v4_test_stateid'),
                        'v4_want_deleg' => array('descr' => 'v4_want_deleg'),
                        'v4_destroy_clid' => array('descr' => 'v4_destroy_clid'),
                        'v4_reclaim_comp' => array('descr' => 'v4_reclaim_comp'),
                );

$i = 0;

if (rrdtool_check_rrd_exists($rrd_filename)) {
    foreach ($array as $ds => $var) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr']    = $var['descr'];
        $rrd_list[$i]['ds']       = $ds;
        $rrd_list[$i]['colour'] = \LibreNMS\Config::get("graph_colours.manycolours.$i");
        $i++;
    }
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_v3_multiline.inc.php';
