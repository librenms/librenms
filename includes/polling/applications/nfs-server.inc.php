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

use LibreNMS\RRD\RrdDefinition;

$name = 'nfs-server';
$oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.10.110.102.115.45.115.101.114.118.101.114';

echo ' ' . $name;

$nfsstats = snmp_get($device, $oid, '-Oqv');

$app_id = $app['app_id'];

// rrd names
$rrd_name = [];
$rrd_name['default'] = ['app', 'nfs-server-default', $app_id];
$rrd_name['proc2'] = ['app', 'nfs-server-proc2', $app_id];
$rrd_name['proc3'] = ['app', 'nfs-server-proc3', $app_id];
$rrd_name['proc4'] = ['app', 'nfs-server-proc4', $app_id];
$rrd_name['proc4ops'] = ['app', 'nfs-server-proc4ops', $app_id];

// rrd definitions
$rrd_def_array['default'] = RrdDefinition::make()
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
        ->addDataset('rpc_badclnt', 'COUNTER', 0, 125000000000);

$rrd_def_array['proc2'] = RrdDefinition::make()
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
        ->addDataset('proc2_fsstat', 'COUNTER', 0, 125000000000);

$rrd_def_array['proc3'] = RrdDefinition::make()
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
        ->addDataset('proc3_commit', 'COUNTER', 0, 125000000000);

$rrd_def_array['proc4'] = RrdDefinition::make()
        ->addDataset('proc4_null', 'COUNTER', 0, 125000000000)
        ->addDataset('proc4_compound', 'COUNTER', 0, 125000000000);

$rrd_def_array['proc4ops'] = RrdDefinition::make()
        ->addDataset('v4_op0-unused', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_op1-unused', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_op2-future', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_access', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_close', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_commit', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_create', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_delegpurge', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_delegreturn', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_getattr', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_getfh', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_link', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_lock', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_lockt', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_locku', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_lookup', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_lookup_root', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_nverify', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_open', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_openattr', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_open_conf', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_open_dgrd', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_putfh', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_putpubfh', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_putrootfh', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_read', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_readdir', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_readlink', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_remove', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_rename', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_renew', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_restorefh', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_savefh', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_secinfo', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_setattr', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_setcltid', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_setcltidconf', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_verify', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_write', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_rellockowner', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_bc_ctl', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_bind_conn', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_exchange_id', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_create_ses', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_destroy_ses', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_free_stateid', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_getdirdeleg', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_getdevinfo', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_getdevlist', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_layoutcommit', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_layoutget', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_layoutreturn', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_secinfononam', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_sequence', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_set_ssv', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_test_stateid', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_want_deleg', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_destroy_clid', 'COUNTER', 0, 125000000000)
        ->addDataset('v4_reclaim_comp', 'COUNTER', 0, 125000000000);

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

$keys_nfs_server = [
    'rc' => ['rc_hits', 'rc_misses', 'rc_nocache'],
    'fh' => ['fh_lookup', 'fh_anon', 'fh_ncachedir', 'fh_ncachenondir', 'fh_stale'],
    'io' => ['io_read', 'io_write'],
    'th' => ['th_threads', 'th_fullcnt', 'th_range01', 'th_range02', 'th_range03', 'th_range04', 'th_range05', 'th_range06', 'th_range07', 'th_range08', 'th_range09', 'th_range10'],
    'ra' => ['ra_cachesize', 'ra_range01', 'ra_range02', 'ra_range03', 'ra_range04', 'ra_range05', 'ra_range06', 'ra_range07', 'ra_range08', 'ra_range09', 'ra_range10', 'ra_notfound'],
    'net' => ['net_cnt', 'net_udp', 'net_tcp', 'net_tcpconn'],
    'rpc' => ['rpc_calls', 'rpc_badcount', 'rpc_badfmt', 'rpc_badauth', 'rpc_badclnt'],
    'proc2' => ['proc2_null', 'proc2_getattr', 'proc2_setattr', 'proc2_root', 'proc2_lookup', 'proc2_readlink', 'proc2_read', 'proc2_wrcache', 'proc2_write', 'proc2_create', 'proc2_remove', 'proc2_rename', 'proc2_link', 'proc2_symlink', 'proc2_mkdir', 'proc2_rmdir', 'proc2_readdir', 'proc2_fsstat'],
    'proc3' => ['proc3_null', 'proc3_getattr', 'proc3_setattr', 'proc3_lookup', 'proc3_access', 'proc3_readlink', 'proc3_read', 'proc3_write', 'proc3_create', 'proc3_mkdir', 'proc3_symlink', 'proc3_mknod', 'proc3_remove', 'proc3_rmdir', 'proc3_rename', 'proc3_link', 'proc3_readdir', 'proc3_readdirplus', 'proc3_fsstat', 'proc3_fsinfo', 'proc3_pathconf', 'proc3_commit'],
    'proc4' => ['proc4_null', 'proc4_compound'],
    'proc4ops' => [
        'v4_op0-unused', 'v4_op1-unused', 'v4_op2-future', 'v4_access', 'v4_close',
        'v4_commit', 'v4_create', 'v4_delegpurge', 'v4_delegreturn', 'v4_getattr', 'v4_getfh',
        'v4_link', 'v4_lock', 'v4_lockt', 'v4_locku', 'v4_lookup', 'v4_lookup_root', 'v4_nverify',
        'v4_open', 'v4_openattr', 'v4_open_conf', 'v4_open_dgrd', 'v4_putfh', 'v4_putpubfh', 'v4_putrootfh',
        'v4_read', 'v4_readdir', 'v4_readlink', 'v4_remove', 'v4_rename', 'v4_renew', 'v4_restorefh', 'v4_savefh',
        'v4_secinfo', 'v4_setattr', 'v4_setcltid', 'v4_setcltidconf', 'v4_verify', 'v4_write', 'v4_rellockowner',
        'v4_bc_ctl', 'v4_bind_conn', 'v4_exchange_id', 'v4_create_ses', 'v4_destroy_ses', 'v4_free_stateid',
        'v4_getdirdeleg', 'v4_getdevinfo', 'v4_getdevlist', 'v4_layoutcommit', 'v4_layoutget', 'v4_layoutreturn',
        'v4_secinfononam', 'v4_sequence', 'v4_set_ssv', 'v4_test_stateid', 'v4_want_deleg', 'v4_destroy_clid',
        'v4_reclaim_comp', ],
];

// parse each output line, by the id
// then 'map' the values to the arrays from $keys_nfs_server
$lines = explode("\n", $nfsstats);
$default_fields = [];
$metrics = [];

foreach ($lines as $line) {
    $line_values = explode(' ', $line);
    $line_id = array_shift($line_values);

    switch ($line_id) {
        case 'rc':
        case 'fh':
        case 'io':
        case 'th':
        case 'ra':
        case 'net':
        case 'rpc':
            // combine keys + values, and then merge it in $fields array
            $default_fields = array_merge($default_fields, array_combine($keys_nfs_server[$line_id], $line_values));
            break;
        case 'proc2':
        case 'proc3':
        case 'proc4':
        case 'proc4ops':
            // note : proc2 is dropped for kernels 3.10.0+ (centos 7+)
            // note : proc4ops has changed a few times, and getting the keys is difficult
            //       I only use the version which reports 59 value's (centos 6)

            // the first value of the proc* is the amount of fields that will follow;
            // we check this, and if its incorrect, do not polute the chart with wrong values
            $value_count = array_shift($line_values);

            if ($value_count == count($keys_nfs_server[$line_id])) {
                $fields = array_combine($keys_nfs_server[$line_id], $line_values);

                // create or push data to rrd
                $tags = ['name' => $name, 'app_id' => $app['app_id'], 'rrd_name' => $rrd_name[$line_id], 'rrd_def' => $rrd_def_array[$line_id]];
                $metrics[$line_id] = $fields;
                data_update($device, 'app', $tags, $fields);
            }
            break;
    }
}
$metrics['none'] = $default_fields;

// push the default nfs server data to rrd
$tags = ['name' => $name, 'app_id' => $app['app_id'], 'rrd_name' => $rrd_name['default'], 'rrd_def' => $rrd_def_array['default']];
data_update($device, 'app', $tags, $default_fields);
update_application($app, $nfsstats, $metrics);

// clean up scope
unset($nfsstats, $rrd_name, $rrd_def_array, $default_fields, $fields, $tags);
