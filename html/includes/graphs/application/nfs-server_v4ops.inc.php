<?php
require 'includes/graphs/common.inc.php';
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
$rrd_filename  = $config['rrd_dir'].'/'.$device['hostname'].'/app-nfs-server-'.$app['app_id'].'.rrd';
$array         = array(
                 'v4ops_access' => array('descr' => 'access'),
                 'v4ops_close' => array('descr' => 'close'),
                 'v4ops_commit' => array('descr' => 'commit'),
                 'v4ops_create' => array('descr' => 'create'),
                 'v4ops_delegpurge' => array('descr' => 'delegpurge'),
                 'v4ops_delegreturn' => array('descr' => 'delegreturn'),
                 'v4ops_getattr' => array('descr' => 'getattr'),
                 'v4ops_getfh' => array('descr' => 'getfh'),
                 'v4ops_link' => array('descr' => 'link'),
                 'v4ops_lock' => array('descr' => 'lock'),
                 'v4ops_lockt' => array('descr' => 'lockt'),
                 'v4ops_locku' => array('descr' => 'locku'),
                 'v4ops_lookup' => array('descr' => 'lookup'),
                 'v4ops_lookup_root' => array('descr' => 'lookup_root'),
                 'v4ops_nverify' => array('descr' => 'nverify'),
                 'v4ops_open' => array('descr' => 'opsen'),
                 'v4ops_openattr' => array('descr' => 'opsenattr'),
                 'v4ops_open_confirm' => array('descr' => 'opsen_confirm'),
                 'v4ops_open_downgrad' => array('descr' => 'opsen_downgrade'),
                 'v4ops_putfh' => array('descr' => 'putfh'),
                 'v4ops_putpubfh' => array('descr' => 'putpubfh'),
                 'v4ops_putrootfh' => array('descr' => 'putrootfh'),
                 'v4ops_read' => array('descr' => 'read'),
                 'v4ops_readdir' => array('descr' => 'readdir'),
                 'v4ops_readlink' => array('descr' => 'readlink'),
                 'v4ops_remove' => array('descr' => 'remove'),
                 'v4ops_rename' => array('descr' => 'rename'),
                 'v4ops_renew' => array('descr' => 'renew'),
                 'v4ops_savefh' => array('descr' => 'savefh'),
                 'v4ops_secinfo' => array('descr' => 'secinfo'),
                 'v4ops_setattr' => array('descr' => 'setattr'),
                 'v4ops_setclientid' => array('descr' => 'setclientid'),
                 'v4ops_setclientid_c' => array('descr' => 'setclientid confirm'),
                 'v4ops_verify' => array('descr' => 'verify'),
                 'v4ops_write' => array('descr' => 'write'),
                 'v4ops_release_locko' => array('descr' => 'release lockowner'),
                 'v4ops_backchannel_c' => array('descr' => 'backchannel ctl'),
                 'v4ops_bind_conn_to_' => array('descr' => 'bind conn to session'),
                 'v4ops_exchange_id' => array('descr' => 'exchange id'),
                 'v4ops_create_sessio' => array('descr' => 'create session'),
                 'v4ops_destroy_sessi' => array('descr' => 'destroy session'),
                 'v4ops_free_stateid' => array('descr' => 'free stateid'),
                 'v4ops_get_dir_deleg' => array('descr' => 'get dir delegation'),
                 'v4ops_getdeviceinfo' => array('descr' => 'getdeviceinfo'),
                 'v4ops_getdevicelist' => array('descr' => 'getdevicelist'),
                 'v4ops_layoutcommit' => array('descr' => 'layoutcommit'),
                 'v4ops_layoutget' => array('descr' => 'layoutget'),
                 'v4ops_secinfo_no_na' => array('descr' => 'secinfo no name'),
                 'v4ops_sequence' => array('descr' => 'sequence'),
                 'v4ops_set_ssv' => array('descr' => 'set ssv'),
                 'v4ops_test_stateid' => array('descr' => 'test stateid'),
                 'v4ops_want_delegati' => array('descr' => 'want delegation'),
                 'v4ops_destroy_clien' => array('descr' => 'destroy clientid'),
                 'v4ops_reclaim_compl' => array('descr' => 'reclaim complete'),
                );

$i = 0;

if (is_file($rrd_filename)) {
    foreach ($array as $ds => $var) {
        $rrd_list[$i]['filename'] = $rrd_filename;
        $rrd_list[$i]['descr']    = $var['descr'];
        $rrd_list[$i]['ds']       = $ds;
        $rrd_list[$i]['colour']   = $config['graph_colours']['manycolours'][$i];
        $i++;
    }
} else {
    echo "file missing: $rrd_filename";
}

require 'includes/graphs/generic_v3_multiline.inc.php';
