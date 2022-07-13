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
$unit_text = 'NFS v4 Operations';
$unitlen = 10;
$bigdescrlen = 15;
$smalldescrlen = 15;
$dostack = 0;
$printtotal = 0;
$addarea = 1;
$transparency = 33;

$rrd_filename = Rrd::name($device['hostname'], ['app', 'nfs-server-proc4ops', $app['app_id']]);

$array = [
    // 'v4_op0-unused' => array('descr' => 'v4_op0-unused'),
    // 'v4_op1-unused' => array('descr' => 'v4_op1-unused'),
    // 'v4_op2-future' => array('descr' => 'v4_op2-future'),
    'v4_access' => ['descr' => 'v4_access'],
    'v4_close' => ['descr' => 'v4_close'],
    'v4_commit' => ['descr' => 'v4_commit'],
    'v4_create' => ['descr' => 'v4_create'],
    'v4_delegpurge' => ['descr' => 'v4_delegpurge'],
    'v4_delegreturn' => ['descr' => 'v4_delegreturn'],
    'v4_getattr' => ['descr' => 'v4_getattr'],
    'v4_getfh' => ['descr' => 'v4_getfh'],
    'v4_link' => ['descr' => 'v4_link'],
    'v4_lock' => ['descr' => 'v4_lock'],
    'v4_lockt' => ['descr' => 'v4_lockt'],
    'v4_locku' => ['descr' => 'v4_locku'],
    'v4_lookup' => ['descr' => 'v4_lookup'],
    'v4_lookup_root' => ['descr' => 'v4_lookup_root'],
    'v4_nverify' => ['descr' => 'v4_nverify'],
    'v4_open' => ['descr' => 'v4_open'],
    'v4_openattr' => ['descr' => 'v4_openattr'],
    'v4_open_conf' => ['descr' => 'v4_open_conf'],
    'v4_open_dgrd' => ['descr' => 'v4_open_dgrd'],
    'v4_putfh' => ['descr' => 'v4_putfh'],
    'v4_putpubfh' => ['descr' => 'v4_putpubfh'],
    'v4_putrootfh' => ['descr' => 'v4_putrootfh'],
    'v4_read' => ['descr' => 'v4_read'],
    'v4_readdir' => ['descr' => 'v4_readdir'],
    'v4_readlink' => ['descr' => 'v4_readlink'],
    'v4_remove' => ['descr' => 'v4_remove'],
    'v4_rename' => ['descr' => 'v4_rename'],
    'v4_renew' => ['descr' => 'v4_renew'],
    'v4_restorefh' => ['descr' => 'v4_restorefh'],
    'v4_savefh' => ['descr' => 'v4_savefh'],
    'v4_secinfo' => ['descr' => 'v4_secinfo'],
    'v4_setattr' => ['descr' => 'v4_setattr'],
    'v4_setcltid' => ['descr' => 'v4_setcltid'],
    'v4_setcltidconf' => ['descr' => 'v4_setcltidconf'],
    'v4_verify' => ['descr' => 'v4_verify'],
    'v4_write' => ['descr' => 'v4_write'],
    'v4_rellockowner' => ['descr' => 'v4_rellockowner'],
    'v4_bc_ctl' => ['descr' => 'v4_bc_ctl'],
    'v4_bind_conn' => ['descr' => 'v4_bind_conn'],
    'v4_exchange_id' => ['descr' => 'v4_exchange_id'],
    'v4_create_ses' => ['descr' => 'v4_create_ses'],
    'v4_destroy_ses' => ['descr' => 'v4_destroy_ses'],
    'v4_free_stateid' => ['descr' => 'v4_free_stateid'],
    'v4_getdirdeleg' => ['descr' => 'v4_getdirdeleg'],
    'v4_getdevinfo' => ['descr' => 'v4_getdevinfo'],
    'v4_getdevlist' => ['descr' => 'v4_getdevlist'],
    'v4_layoutcommit' => ['descr' => 'v4_layoutcommit'],
    'v4_layoutget' => ['descr' => 'v4_layoutget'],
    'v4_layoutreturn' => ['descr' => 'v4_layoutreturn'],
    'v4_secinfononam' => ['descr' => 'v4_secinfononam'],
    'v4_sequence' => ['descr' => 'v4_sequence'],
    'v4_set_ssv' => ['descr' => 'v4_set_ssv'],
    'v4_test_stateid' => ['descr' => 'v4_test_stateid'],
    'v4_want_deleg' => ['descr' => 'v4_want_deleg'],
    'v4_destroy_clid' => ['descr' => 'v4_destroy_clid'],
    'v4_reclaim_comp' => ['descr' => 'v4_reclaim_comp'],
];

$i = 0;

if (Rrd::checkRrdExists($rrd_filename)) {
    foreach ($array as $ds => $var) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr'] = $var['descr'];
        $rrd_list[$i]['ds'] = $ds;
        $rrd_list[$i]['colour'] = \LibreNMS\Config::get("graph_colours.manycolours.$i");
        $i++;
    }
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/html/graphs/generic_v3_multiline.inc.php';
