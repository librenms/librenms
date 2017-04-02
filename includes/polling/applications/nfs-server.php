<?php

use LibreNMS\RRD\RrdDefinition;
$name = 'nfs-server';
$app_id = $app['app_id'];
$oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.10.110.102.115.45.115.101.114.118.101.114';

echo ' ' . $name;

$nfsstats = snmp_walk($device, $oid, '-Oqv', 'NET-SNMP-EXTEND-MIB');
update_application($app, $nfsstats);

$rrd_name = array('app', 'nfs-server', $app_id);
$rrd_def = RrdDefinition::make()
		->addDataset('rc_hits', 'COUNTER', 0, 125000000000)
		->addDataset('rc_misses', 'COUNTER', 0, 125000000000)
		->addDataset('rc_nocache', 'COUNTER', 0, 125000000000)
		
		->addDataset('fh_lookup', 'COUNTER', 0, 125000000000)
		->addDataset('fh_anon', 'COUNTER', 0, 125000000000)
		->addDataset('fh_ncachedir', 'COUNTER', 0, 125000000000)
		->addDataset('fh_ncachenondir', 'COUNTER', 0, 125000000000)
		->addDataset('fh_stale', 'COUNTER', 0, 125000000000)
		
		->addDataset('io_read', 'COUNTER', 0, 125000000000)
		->addDataset('io_write', 'COUNTER', 0, 125000000000)
		
		->addDataset('th_threads', 'COUNTER', 0, 125000000000)
		->addDataset('th_fullcnt', 'COUNTER', 0, 125000000000)
		->addDataset('th_range01', 'GAUGE', 0, 125000000000)
		->addDataset('th_range02', 'GAUGE', 0, 125000000000)
		->addDataset('th_range03', 'GAUGE', 0, 125000000000)
		->addDataset('th_range04', 'GAUGE', 0, 125000000000)
		->addDataset('th_range05', 'GAUGE', 0, 125000000000)
		->addDataset('th_range06', 'GAUGE', 0, 125000000000)
		->addDataset('th_range07', 'GAUGE', 0, 125000000000)
		->addDataset('th_range08', 'GAUGE', 0, 125000000000)
		->addDataset('th_range09', 'GAUGE', 0, 125000000000)
		->addDataset('th_range10', 'GAUGE', 0, 125000000000)
		
		->addDataset('ra_cachesize', 'GAUGE', 0, 125000000000)
		->addDataset('ra_range01', 'GAUGE', 0, 125000000000)
		->addDataset('ra_range02', 'GAUGE', 0, 125000000000)
		->addDataset('ra_range03', 'GAUGE', 0, 125000000000)
		->addDataset('ra_range04', 'GAUGE', 0, 125000000000)
		->addDataset('ra_range05', 'GAUGE', 0, 125000000000)
		->addDataset('ra_range06', 'GAUGE', 0, 125000000000)
		->addDataset('ra_range07', 'GAUGE', 0, 125000000000)
		->addDataset('ra_range08', 'GAUGE', 0, 125000000000)
		->addDataset('ra_range09', 'GAUGE', 0, 125000000000)
		->addDataset('ra_range10', 'GAUGE', 0, 125000000000)
		->addDataset('ra_notfound', 'GAUGE', 0, 125000000000)
		
		->addDataset('net_cnt', 'COUNTER', 0, 125000000000)
		->addDataset('net_udp', 'COUNTER', 0, 125000000000)
		->addDataset('net_tcp', 'COUNTER', 0, 125000000000)
		->addDataset('net_tcpconn', 'COUNTER', 0, 125000000000)
		
		->addDataset('rpc_calls', 'COUNTER', 0, 125000000000)
		->addDataset('rpc_badcount', 'COUNTER', 0, 125000000000)
		->addDataset('rpc_badfmt', 'COUNTER', 0, 125000000000)
		->addDataset('rpc_badauth', 'COUNTER', 0, 125000000000)
		->addDataset('rpc_badclnt', 'COUNTER', 0, 125000000000)
		
		->addDataset('proc2_null', 'COUNTER', 0, 125000000000)
		->addDataset('proc2_getattr', 'COUNTER', 0, 125000000000)
		->addDataset('proc2_setattr', 'COUNTER', 0, 125000000000)
		->addDataset('proc2_root', 'COUNTER', 0, 125000000000)
		->addDataset('proc2_lookup', 'COUNTER', 0, 125000000000)
		->addDataset('proc2_readlink', 'COUNTER', 0, 125000000000)
		->addDataset('proc2_read', 'COUNTER', 0, 125000000000)
		->addDataset('proc2_wrcache', 'COUNTER', 0, 125000000000)
		->addDataset('proc2_write', 'COUNTER', 0, 125000000000)
		->addDataset('proc2_create', 'COUNTER', 0, 125000000000)
		->addDataset('proc2_remove', 'COUNTER', 0, 125000000000)
		->addDataset('proc2_rename', 'COUNTER', 0, 125000000000)
		->addDataset('proc2_link', 'COUNTER', 0, 125000000000)
		->addDataset('proc2_symlink', 'COUNTER', 0, 125000000000)
		->addDataset('proc2_mkdir', 'COUNTER', 0, 125000000000)
		->addDataset('proc2_rmdir', 'COUNTER', 0, 125000000000)
		->addDataset('proc2_readdir', 'COUNTER', 0, 125000000000)
		->addDataset('proc2_fsstat', 'COUNTER', 0, 125000000000)
		
		->addDataset('proc3_null', 'COUNTER', 0, 125000000000)
		->addDataset('proc3_getattr', 'COUNTER', 0, 125000000000)
		->addDataset('proc3_setattr', 'COUNTER', 0, 125000000000)
		->addDataset('proc3_lookup', 'COUNTER', 0, 125000000000)
		->addDataset('proc3_access', 'COUNTER', 0, 125000000000)
		->addDataset('proc3_readlink', 'COUNTER', 0, 125000000000)
		->addDataset('proc3_read', 'COUNTER', 0, 125000000000)
		->addDataset('proc3_write', 'COUNTER', 0, 125000000000)
		->addDataset('proc3_create', 'COUNTER', 0, 125000000000)
		->addDataset('proc3_mkdir', 'COUNTER', 0, 125000000000)
		->addDataset('proc3_symlink', 'COUNTER', 0, 125000000000)
		->addDataset('proc3_mknod', 'COUNTER', 0, 125000000000)
		->addDataset('proc3_remove', 'COUNTER', 0, 125000000000)
		->addDataset('proc3_rmdir', 'COUNTER', 0, 125000000000)
		->addDataset('proc3_rename', 'COUNTER', 0, 125000000000)
		->addDataset('proc3_link', 'COUNTER', 0, 125000000000)
		->addDataset('proc3_readdir', 'COUNTER', 0, 125000000000)
		->addDataset('proc3_readdirplus', 'COUNTER', 0, 125000000000)
		->addDataset('proc3_fsstat', 'COUNTER', 0, 125000000000)
		->addDataset('proc3_fsinfo', 'COUNTER', 0, 125000000000)
		->addDataset('proc3_pathconf', 'COUNTER', 0, 125000000000)
		->addDataset('proc3_commit', 'COUNTER', 0, 125000000000)
		
		->addDataset('proc4_null', 'COUNTER', 0, 125000000000)
		->addDataset('proc4_compound', 'COUNTER', 0, 125000000000)
		
		->addDataset('v4ops_access', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_close', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_commit', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_create', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_delegpurge', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_delegreturn', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_getattr', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_getfh', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_link', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_lock', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_lockt', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_locku', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_lookup', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_lookup_root', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_nverify', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_open', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_openattr', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_open_confirm', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_open_downgrad', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_putfh', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_putpubfh', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_putrootfh', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_read', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_readdir', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_readlink', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_remove', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_rename', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_renew', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_restorefh', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_savefh', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_secinfo', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_setattr', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_setclientid', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_setclientid_c', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_verify', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_write', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_release_locko', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_backchannel_c', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_bind_conn_to_', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_exchange_id', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_create_sessio', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_destroy_sessi', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_free_stateid', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_get_dir_deleg', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_getdeviceinfo', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_getdevicelist', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_layoutcommit', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_layoutget', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_secinfo_no_na', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_sequence', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_set_ssv', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_test_stateid', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_want_delegati', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_destroy_clien', 'COUNTER', 0, 125000000000)
		->addDataset('v4ops_reclaim_compl', 'COUNTER', 0, 125000000000)
;

/* examples output :
rc 0 87795065 629022724
fh 0 0 0 0 0
io 35586909 1515531914
th 16 0 0.000 0.000 0.000 0.000 0.000 0.000 0.000 0.000 0.000 0.000
ra 32 229333249 0 0 0 0 0 0 0 0 0 4106423
net 717504610 0 717216613 15750
rpc 717521317 0 0 0 0
proc2 18 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0
proc3 22 84 185701663 9587314 62436191 114380547 61635 233519348 60991846 8887178 203052 68539 0 5816369 163267 1946736 127750 35510 8641639 1060644 112 56 13833978
proc4 2 1 404
proc4ops 59 0 0 0 2 0 0 0 0 0 402 3 0 0 0 0 3 0 0 0 0 0 0 403 0 1 0 1 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0
*/

$keys_nfs_server = array(
							'rc' => array('th_hits', 'th_misses', 'th_nocache'),
							'fh' => array('fh_lookup', 'fh_anon', 'fh_ncachedir', 'fh_ncachenondir', 'fh_stale'),
							'io' => array('io_read', 'io_write'),
							'th' => array('th_threads', 'th_fullcnt', 'th_range01', 'th_range02', 'th_range03', 'th_range04', 'th_range05', 'th_range06', 'th_range07', 'th_range08', 'th_range09', 'th_range10'),
							'ra' => array('ra_cachesize', 'ra_range01', 'ra_range02', 'ra_range03', 'ra_range04', 'ra_range05', 'ra_range06', 'ra_range07', 'ra_range08', 'ra_range09', 'ra_range10', 'ra_notfound'),
							'net' => array('net_cnt', 'net_udp', 'net_tcp', 'net_tcpconn'),
							'rpc' => array('rpc_calls', 'rpc_badcount', 'rpc_badfmt', 'rpc_badauth', 'rpc_badclnt'),
							'proc2' => array('proc2_null', 'proc2_getattr', 'proc2_setattr', 'proc2_root', 'proc2_lookup','proc2_readlink', 'proc2_read', 'proc2_wrcache', 'proc2_write', 'proc2_create','proc2_remove', 'proc2_rename', 'proc2_link', 'proc2_symlink', 'proc2_mkdir','proc2_rmdir', 'proc2_readdir', 'proc2_fsstat'),
							'proc3' => array('proc3_null', 'proc3_getattr', 'proc3_setattr', 'proc3_lookup', 'proc3_access','proc3_readlink', 'proc3_read', 'proc3_write', 'proc3_create', 'proc3_mkdir','proc3_symlink', 'proc3_mknod', 'proc3_remove', 'proc3_rmdir', 'proc3_rename','proc3_link', 'proc3_readdir', 'proc3_readdirplus', 'proc3_fsstat', 'proc3_fsinfo', 'proc3_pathconf', 'proc3_commit'),
							'proc4' => array('proc4_null', 'proc4_compound'),
							'proc4ops' => array(
												'v4ops_access','v4ops_close','v4ops_commit','v4ops_create','v4ops_delegpurge','v4ops_delegreturn','v4ops_getattr','v4ops_getfh','v4ops_link',
												'v4ops_lock','v4ops_lockt','v4ops_locku','v4ops_lookup','v4ops_lookup_root','v4ops_nverify','v4ops_open','v4ops_openattr','v4ops_open_confirm',
												'v4ops_open_downgrad','v4ops_putfh','v4ops_putpubfh','v4ops_putrootfh','v4ops_read','v4ops_readdir','v4ops_readlink','v4ops_remove','v4ops_rename',
												'v4ops_renew','v4ops_restorefh','v4ops_savefh','v4ops_secinfo','v4ops_setattr','v4ops_setclientid','v4ops_setclientid_c','v4ops_verify',
												'v4ops_write','v4ops_release_locko','v4ops_backchannel_c','v4ops_bind_conn_to_','v4ops_exchange_id','v4ops_create_sessio','v4ops_destroy_sessi',
												'v4ops_free_stateid','v4ops_get_dir_deleg','v4ops_getdeviceinfo','v4ops_getdevicelist','v4ops_layoutcommit','v4ops_layoutget','v4ops_secinfo_no_na',
												'v4ops_sequence','v4ops_set_ssv','v4ops_test_stateid','v4ops_want_delegati','v4ops_destroy_clien','v4ops_reclaim_compl')
						);

						
# parse each output line, by the id
# then 'map' the values to the arrays from $keys_nfs_server
$lines 	= explode("\n", $nfsstats);
$fields = array();
foreach ($lines as $line)
{
	$line_values 	= split(" ", $line);
	$line_id 		= $line_values[0];
	
	# remove the line_id
	array_shift($line_values);
	
	switch ($line_id)
	{
		case 'rc':
		case 'fh':
		case 'io':
		case 'th':
		case 'ra':
		case 'net': 
		case 'rpc': 
			# combine keys + values, and then merge it in $fields array
			$fields = array_merge($fields, array_combine($keys_nfs_server['rc'], $line_values));
		break;
		case 'proc2': 
		case 'proc3': 
		case 'proc4':
		case 'proc4ops': 
			# note : proc2 is dropped for kernels 3.10.0+ (centos 7+)
			# note : proc4ops has changed a few times, and getting the keys is difficult
			#		 I only use the version which reports 59 value's (centos 6)
			
			# the first value of the proc* is the amount of fields that will follow;
			# we check this, and if its incorrect, do not polute the chart with wrong values
			$value_count = $line_values[0];
			if ($value_count == count($keys_nfs_server[$line_id]))
			{
				# pop the value_count
				array_shift($line_values);
				$fields = array_merge($fields, array_combine($keys_nfs_server[$line_id], $line_values));
			}
		break;
	}
}

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);

unset($nfsstats, $rrd_name, $rrd_def, $data, $fields, $tags);