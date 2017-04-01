<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'nfs-server';
$app_id = $app[app_id];
$oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.10.110.102.115.45.115.101.114.118.101.114';

echo ' ' . $name;

$nfsstats = snmp_walk($device, $oid, '-Oqv', 'NET-SNMP-EXTEND-MIB');
update_application($app, $nfsstats);

$rrd_name = array('app', 'nfs-server', $app_id);
$rrd_def = RrdDefinition::make()
		->addDataset('rc_hits', 'GAUGE', 0)
		->addDataset('rc_misses', 'GAUGE', 0)
		->addDataset('rc_nocache', 'GAUGE', 0)
		->addDataset('fh_lookup', 'GAUGE', 0)
		->addDataset('fh_anon', 'GAUGE', 0)
		->addDataset('fh_ncachedir', 'GAUGE', 0)
		->addDataset('fh_ncachenondir', 'GAUGE', 0)
		->addDataset('fh_stale', 'GAUGE', 0)
		->addDataset('io_read', 'GAUGE', 0)
		->addDataset('io_write', 'GAUGE', 0)
		->addDataset('ra_range01', 'GAUGE', 0)
		->addDataset('ra_range02', 'GAUGE', 0)
		->addDataset('ra_range03', 'GAUGE', 0)
		->addDataset('ra_range04', 'GAUGE', 0)
		->addDataset('ra_range05', 'GAUGE', 0)
		->addDataset('ra_range06', 'GAUGE', 0)
		->addDataset('ra_range07', 'GAUGE', 0)
		->addDataset('ra_range08', 'GAUGE', 0)
		->addDataset('ra_range09', 'GAUGE', 0)
		->addDataset('ra_range10', 'GAUGE', 0)
		->addDataset('ra_notfound', 'GAUGE', 0)
		->addDataset('net_all', 'GAUGE', 0)
		->addDataset('net_udp', 'GAUGE', 0)
		->addDataset('net_tcp', 'GAUGE', 0)
		->addDataset('net_tcpconn', 'GAUGE', 0)
		->addDataset('rpc_calls', 'GAUGE', 0)
		->addDataset('rpc_badfmt', 'GAUGE', 0)
		->addDataset('rpc_badauth', 'GAUGE', 0)
		->addDataset('rpc_badclnt', 'GAUGE', 0)
		->addDataset('proc2_null', 'GAUGE', 0)
		->addDataset('proc2_getattr', 'GAUGE', 0)
		->addDataset('proc2_setattr', 'GAUGE', 0)
		->addDataset('proc2_root', 'GAUGE', 0)
		->addDataset('proc2_lookup', 'GAUGE', 0)
		->addDataset('proc2_readlink', 'GAUGE', 0)
		->addDataset('proc2_read', 'GAUGE', 0)
		->addDataset('proc2_wrcache', 'GAUGE', 0)
		->addDataset('proc2_write', 'GAUGE', 0)
		->addDataset('proc2_create', 'GAUGE', 0)
		->addDataset('proc2_remove', 'GAUGE', 0)
		->addDataset('proc2_rename', 'GAUGE', 0)
		->addDataset('proc2_link', 'GAUGE', 0)
		->addDataset('proc2_symlink', 'GAUGE', 0)
		->addDataset('proc2_mkdir', 'GAUGE', 0)
		->addDataset('proc2_rmdir', 'GAUGE', 0)
		->addDataset('proc2_readdir', 'GAUGE', 0)
		->addDataset('proc2_fsstat', 'GAUGE', 0)
		->addDataset('proc3_null', 'GAUGE', 0)
		->addDataset('proc3_getattr', 'GAUGE', 0)
		->addDataset('proc3_setattr', 'GAUGE', 0)
		->addDataset('proc3_lookup', 'GAUGE', 0)
		->addDataset('proc3_access', 'GAUGE', 0)
		->addDataset('proc3_readlink', 'GAUGE', 0)
		->addDataset('proc3_read', 'GAUGE', 0)
		->addDataset('proc3_write', 'GAUGE', 0)
		->addDataset('proc3_create', 'GAUGE', 0)
		->addDataset('proc3_mkdir', 'GAUGE', 0)
		->addDataset('proc3_symlink', 'GAUGE', 0)
		->addDataset('proc3_mknod', 'GAUGE', 0)
		->addDataset('proc3_remove', 'GAUGE', 0)
		->addDataset('proc3_rmdir', 'GAUGE', 0)
		->addDataset('proc3_rename', 'GAUGE', 0)
		->addDataset('proc3_link', 'GAUGE', 0)
		->addDataset('proc3_readdir', 'GAUGE', 0)
		->addDataset('proc3_readdirplus', 'GAUGE', 0)
		->addDataset('proc3_fsstat', 'GAUGE', 0)
		->addDataset('proc3_fsinfo', 'GAUGE', 0)
		->addDataset('proc3_pathconf', 'GAUGE', 0)
		->addDataset('proc3_commit', 'GAUGE', 0)
		->addDataset('proc4_null', 'GAUGE', 0)
		->addDataset('proc4_compound', 'GAUGE', 0)
		->addDataset('v4ops_access', 'GAUGE', 0)
		->addDataset('v4ops_close', 'GAUGE', 0)
		->addDataset('v4ops_commit', 'GAUGE', 0)
		->addDataset('v4ops_create', 'GAUGE', 0)
		->addDataset('v4ops_delegpurge', 'GAUGE', 0)
		->addDataset('v4ops_delegreturn', 'GAUGE', 0)
		->addDataset('v4ops_getattr', 'GAUGE', 0)
		->addDataset('v4ops_getfh', 'GAUGE', 0)
		->addDataset('v4ops_link', 'GAUGE', 0)
		->addDataset('v4ops_lock', 'GAUGE', 0)
		->addDataset('v4ops_lockt', 'GAUGE', 0)
		->addDataset('v4ops_locku', 'GAUGE', 0)
		->addDataset('v4ops_lookup', 'GAUGE', 0)
		->addDataset('v4ops_lookup_root', 'GAUGE', 0)
		->addDataset('v4ops_nverify', 'GAUGE', 0)
		->addDataset('v4ops_open', 'GAUGE', 0)
		->addDataset('v4ops_openattr', 'GAUGE', 0)
		->addDataset('v4ops_open_confirm', 'GAUGE', 0)
		->addDataset('v4ops_open_downgrad', 'GAUGE', 0)
		->addDataset('v4ops_putfh', 'GAUGE', 0)
		->addDataset('v4ops_putpubfh', 'GAUGE', 0)
		->addDataset('v4ops_putrootfh', 'GAUGE', 0)
		->addDataset('v4ops_read', 'GAUGE', 0)
		->addDataset('v4ops_readdir', 'GAUGE', 0)
		->addDataset('v4ops_readlink', 'GAUGE', 0)
		->addDataset('v4ops_remove', 'GAUGE', 0)
		->addDataset('v4ops_rename', 'GAUGE', 0)
		->addDataset('v4ops_renew', 'GAUGE', 0)
		->addDataset('v4ops_restorefh', 'GAUGE', 0)
		->addDataset('v4ops_savefh', 'GAUGE', 0)
		->addDataset('v4ops_secinfo', 'GAUGE', 0)
		->addDataset('v4ops_setattr', 'GAUGE', 0)
		->addDataset('v4ops_setclientid', 'GAUGE', 0)
		->addDataset('v4ops_setclientid_c', 'GAUGE', 0)
		->addDataset('v4ops_verify', 'GAUGE', 0)
		->addDataset('v4ops_write', 'GAUGE', 0)
		->addDataset('v4ops_release_locko', 'GAUGE', 0)
		->addDataset('v4ops_backchannel_c', 'GAUGE', 0)
		->addDataset('v4ops_bind_conn_to_', 'GAUGE', 0)
		->addDataset('v4ops_exchange_id', 'GAUGE', 0)
		->addDataset('v4ops_create_sessio', 'GAUGE', 0)
		->addDataset('v4ops_destroy_sessi', 'GAUGE', 0)
		->addDataset('v4ops_free_stateid', 'GAUGE', 0)
		->addDataset('v4ops_get_dir_deleg', 'GAUGE', 0)
		->addDataset('v4ops_getdeviceinfo', 'GAUGE', 0)
		->addDataset('v4ops_getdevicelist', 'GAUGE', 0)
		->addDataset('v4ops_layoutcommit', 'GAUGE', 0)
		->addDataset('v4ops_layoutget', 'GAUGE', 0)
		->addDataset('v4ops_secinfo_no_na', 'GAUGE', 0)
		->addDataset('v4ops_sequence', 'GAUGE', 0)
		->addDataset('v4ops_set_ssv', 'GAUGE', 0)
		->addDataset('v4ops_test_stateid', 'GAUGE', 0)
		->addDataset('v4ops_want_delegati', 'GAUGE', 0)
		->addDataset('v4ops_destroy_clien', 'GAUGE', 0)
		->addDataset('v4ops_reclaim_compl', 'GAUGE', 0)
;

$data = explode("|", base64_decode($nfsstats));

$fields = array(
				'rc_hits' => $data[0],
				'rc_misses' => $data[1],
				'rc_nocache' => $data[2],

				'fh_lookup' => $data[3],
				'fh_anon' => $data[4],
				'fh_ncachedir' => $data[5],
				'fh_ncachenondir' => $data[6],
				'fh_stale' => $data[7],

				'io_read' => $data[8],
				'io_write' => $data[9],

				'ra_range01' => $data[10],
				'ra_range02' => $data[11],
				'ra_range03' => $data[12],
				'ra_range04' => $data[13],
				'ra_range05' => $data[14],
				'ra_range06' => $data[15],
				'ra_range07' => $data[16],
				'ra_range08' => $data[17],
				'ra_range09' => $data[18],
				'ra_range10' => $data[19],
				'ra_notfound' => $data[20],

				'net_all' => $data[21],
				'net_udp' => $data[22],
				'net_tcp' => $data[23],
				'net_tcpconn' => $data[24],

				'rpc_calls' => $data[25],
				'rpc_badfmt' => $data[26],
				'rpc_badauth' => $data[27],
				'rpc_badclnt' => $data[28],

				'proc2_null' => $data[29],
				'proc2_getattr' => $data[30],
				'proc2_setattr' => $data[31],
				'proc2_root' => $data[32],
				'proc2_lookup' => $data[33],
				'proc2_readlink' => $data[34],
				'proc2_read' => $data[35],
				'proc2_wrcache' => $data[36],
				'proc2_write' => $data[37],
				'proc2_create' => $data[38],
				'proc2_remove' => $data[39],
				'proc2_rename' => $data[40],
				'proc2_link' => $data[41],
				'proc2_symlink' => $data[42],
				'proc2_mkdir' => $data[43],
				'proc2_rmdir' => $data[44],
				'proc2_readdir' => $data[45],
				'proc2_fsstat' => $data[46],

				'proc3_null' => $data[47],
				'proc3_getattr' => $data[48],
				'proc3_setattr' => $data[49],
				'proc3_lookup' => $data[50],
				'proc3_access' => $data[51],
				'proc3_readlink' => $data[52],
				'proc3_read' => $data[53],
				'proc3_write' => $data[54],
				'proc3_create' => $data[55],
				'proc3_mkdir' => $data[56],
				'proc3_symlink' => $data[57],
				'proc3_mknod' => $data[58],
				'proc3_remove' => $data[59],
				'proc3_rmdir' => $data[60],
				'proc3_rename' => $data[61],
				'proc3_link' => $data[62],
				'proc3_readdir' => $data[63],
				'proc3_readdirplus' => $data[64],
				'proc3_fsstat' => $data[65],
				'proc3_fsinfo' => $data[66],
				'proc3_pathconf' => $data[67],
				'proc3_commit' => $data[68],

				'proc4_null' => $data[69],
				'proc4_compound' => $data[70],
				
				'v4ops_access' => $data[71],
				'v4ops_close' => $data[72],
				'v4ops_commit' => $data[73],
				'v4ops_create' => $data[74],
				'v4ops_delegpurge' => $data[75],
				'v4ops_delegreturn' => $data[76],
				'v4ops_getattr' => $data[77],
				'v4ops_getfh' => $data[78],
				'v4ops_link' => $data[79],
				'v4ops_lock' => $data[80],
				'v4ops_lockt' => $data[81],
				'v4ops_locku' => $data[82],
				'v4ops_lookup' => $data[83],
				'v4ops_lookup_root' => $data[84],
				'v4ops_nverify' => $data[85],
				'v4ops_opsen' => $data[86],
				'v4ops_opsenattr' => $data[87],
				'v4ops_opsen_confirm' => $data[88],
				'v4ops_opsen_downgrade' => $data[89],
				'v4ops_putfh' => $data[90],
				'v4ops_putpubfh' => $data[91],
				'v4ops_putrootfh' => $data[92],
				'v4ops_read' => $data[93],
				'v4ops_readdir' => $data[94],
				'v4ops_readlink' => $data[95],
				'v4ops_remove' => $data[96],
				'v4ops_rename' => $data[97],
				'v4ops_renew' => $data[98],
				'v4ops_restorefh' => $data[99],
				'v4ops_savefh' => $data[100],
				'v4ops_secinfo' => $data[101],
				'v4ops_setattr' => $data[102],
				'v4ops_setclientid' => $data[103],
				'v4ops_setclientid_confirm' => $data[104],
				'v4ops_verify' => $data[105],
				'v4ops_write' => $data[106],
				'v4ops_release_lockowner' => $data[107],
				'v4ops_backchannel_ctl' => $data[108],
				'v4ops_bind_conn_to_session' => $data[109],
				'v4ops_exchange_id' => $data[110],
				'v4ops_create_session' => $data[111],
				'v4ops_destroy_session' => $data[112],
				'v4ops_free_stateid' => $data[113],
				'v4ops_get_dir_delegation' => $data[114],
				'v4ops_getdeviceinfo' => $data[115],
				'v4ops_getdevicelist' => $data[116],
				'v4ops_layoutcommit' => $data[117],
				'v4ops_layoutget' => $data[118],
				'v4ops_secinfo_no_name' => $data[119],
				'v4ops_sequence' => $data[120],
				'v4ops_set_ssv' => $data[121],
				'v4ops_test_stateid' => $data[122],
				'v4ops_want_delegation' => $data[123],
				'v4ops_destroy_clientid' => $data[124],
				'v4ops_reclaim_complete' => $data[125]
);

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);

unset($nfsstats, $rrd_name, $rrd_def, $data, $fields, $tags);