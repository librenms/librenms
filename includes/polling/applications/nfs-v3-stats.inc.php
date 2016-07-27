<?php
$rrd_filename = $config['rrd_dir'].'/'.$device['hostname'].'/app-nfs-stats-'.$app['app_id'].'.rrd';
$options      = '-O qv';
$mib          = 'NET-SNMP-EXTEND-MIB';
$oid	      = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.7.110.102.115.115.116.97.116';
echo 'nfs-v3-stats';

$nfsstats = snmp_walk($device, $oid, $options, $mib);

list($ra_size,$rc_hits,$rc_misses,$rc_nocache,$fh_lookup,$fh_anon,$fh_ncachedir,$fh_ncachenondir,$fh_stale,$io_read,$io_write,$ra_range01,$ra_range02,$ra_range03,$ra_range04,$ra_range05,$ra_range06,$ra_range07,$ra_range08,$ra_range09,$ra_range10,$ra_notfound,$net_all,$net_udp,$net_tcp,$net_tcpconn,$rpc_calls,$rpc_badcalls,$rpc_badfmt,$rpc_badauth,$rpc_badclnt,$proc3_null,$proc3_getattr,$proc3_setattr,$proc3_lookup,$proc3_access,$proc3_readlink,$proc3_read,$proc3_write,$proc3_create,$proc3_mkdir,$proc3_symlink,$proc3_mknod,$proc3_remove,$proc3_rmdir,$proc3_rename,$proc3_link,$proc3_readdir,$proc3_readdirplus,$proc3_fsstat,$proc3_fsinfo,$proc3_pathconf,$proc3_commit) = explode("\n",$nfsstats);

if(!is_file($rrd_filename))
{
  rrdtool_create(
    $rrd_filename,
    '--step 300
    DS:rc_hits:GAUGE:600:0:U
    DS:rc_misses:GAUGE:600:0:U
    DS:rc_nocache:GAUGE:600:0:U
    DS:fh_lookup:GAUGE:600:0:U
    DS:fh_anon:GAUGE:600:0:U
    DS:fh_ncachedir:GAUGE:600:0:U
    DS:fh_ncachenondir:GAUGE:600:0:U
    DS:fh_stale:GAUGE:600:0:U
    DS:io_read:GAUGE:600:0:U
    DS:io_write:GAUGE:600:0:U
    DS:ra_size:GAUGE:600:0:U
    DS:ra_range01:GAUGE:600:0:U
    DS:ra_range02:GAUGE:600:0:U
    DS:ra_range03:GAUGE:600:0:U
    DS:ra_range04:GAUGE:600:0:U
    DS:ra_range05:GAUGE:600:0:U
    DS:ra_range06:GAUGE:600:0:U
    DS:ra_range07:GAUGE:600:0:U
    DS:ra_range08:GAUGE:600:0:U
    DS:ra_range09:GAUGE:600:0:U
    DS:ra_range10:GAUGE:600:0:U
    DS:ra_notfound:GAUGE:600:0:U
    DS:net_all:GAUGE:600:0:U
    DS:net_udp:GAUGE:600:0:U
    DS:net_tcp:GAUGE:600:0:U
    DS:net_tcpconn:GAUGE:600:0:U
    DS:rpc_calls:GAUGE:600:0:U
    DS:rpc_badcalls:GAUGE:600:0:U
    DS:rpc_badfmt:GAUGE:600:0:U
    DS:rpc_badauth:GAUGE:600:0:U
    DS:rpc_badclnt:GAUGE:600:0:U
    DS:proc3_null:GAUGE:600:0:U
    DS:proc3_getattr:GAUGE:600:0:U
    DS:proc3_setattr:GAUGE:600:0:U
    DS:proc3_lookup:GAUGE:600:0:U
    DS:proc3_access:GAUGE:600:0:U
    DS:proc3_readlink:GAUGE:600:0:U
    DS:proc3_read:GAUGE:600:0:U
    DS:proc3_write:GAUGE:600:0:U
    DS:proc3_create:GAUGE:600:0:U
    DS:proc3_mkdir:GAUGE:600:0:U
    DS:proc3_symlink:GAUGE:600:0:U
    DS:proc3_mknod:GAUGE:600:0:U
    DS:proc3_remove:GAUGE:600:0:U
    DS:proc3_rmdir:GAUGE:600:0:U
    DS:proc3_rename:GAUGE:600:0:U
    DS:proc3_link:GAUGE:600:0:U
    DS:proc3_readdir:GAUGE:600:0:U
    DS:proc3_readdirplus:GAUGE:600:0:U
    DS:proc3_fsstat:GAUGE:600:0:U
    DS:proc3_fsinfo:GAUGE:600:0:U
    DS:proc3_pathconf:GAUGE:600:0:U
    DS:proc3_commit:GAUGE:600:0:U
    '.$config['rrd_rra']
  );
}

$fields = array(
    'rc_hits' => $rc_hits,
    'rc_misses' => $rc_misses,
    'rc_nocache' => $rc_nocache,
    'fh_lookup' => $fh_lookup,
    'fh_anon' => $fh_anon,
    'fh_ncachedir' => $fh_ncachedir,
    'fh_ncachenondir' => $fh_ncachenondir,
    'fh_stale' => $fh_stale,
    'io_read' => $io_read,
    'io_write' => $io_write,
    'ra_size' => $ra_size,
    'ra_range01' => $ra_range01,
    'ra_range02' => $ra_range02,
    'ra_range03' => $ra_range03,
    'ra_range04' => $ra_range04,
    'ra_range05' => $ra_range05,
    'ra_range06' => $ra_range06,
    'ra_range07' => $ra_range07,
    'ra_range08' => $ra_range08,
    'ra_range09' => $ra_range09,
    'ra_range10' => $ra_range10,
    'ra_notfound'=> $ra_notfound,
    'net_all' => $net_all,
    'net_udp' => $net_udp,
    'net_tcp' => $net_tcp,
    'net_tcpconn' => $net_tcpconn,
    'rpc_calls' => $rpc_calls,
    'rpc_badcalls' => $rpc_badcalls,
    'rpc_badfmt' => $rpc_badfmt,
    'rpc_badauth' => $rpc_badauth,
    'rpc_badclnt' => $rpc_badclnt,
    'proc3_null' => $proc3_null,
    'proc3_getattr' => $proc3_getattr,
    'proc3_setattr' => $proc3_setattr,
    'proc3_lookup' => $proc3_lookup,
    'proc3_access' => $proc3_access,
    'proc3_readlink' => $proc3_readlink,
    'proc3_read' => $proc3_read,
    'proc3_write' => $proc3_write,
    'proc3_create' => $proc3_create,
    'proc3_mkdir' => $proc3_mkdir,
    'proc3_symlink' => $proc3_symlink,
    'proc3_mknod' => $proc3_mknod,
    'proc3_remove' => $proc3_remove,
    'proc3_rmdir' => $proc3_rmdir,
    'proc3_rename' => $proc3_rename,
    'proc3_link' => $proc3_link,
    'proc3_readdir' => $proc3_readdir,
    'proc3_readdirplus' => $proc3_readdirplus,
    'proc3_fsstat' => $proc3_fsstat,
    'proc3_fsinfo' => $proc3_fsinfo,
    'proc3_pathconf' => $proc3_pathconf,
    'proc3_commit' => $proc3_commit,
); 

rrdtool_update($rrd_filename, $fields);
$tags = array('name' => 'nfs-v3-stats', 'app_id' => $app['app_id']);
influx_update($device,'app',$tags,$fields);
