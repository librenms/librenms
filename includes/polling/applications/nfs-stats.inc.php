<?php
$rrd_filename = $config['rrd_dir'].'/'.$device['hostname'].'/app-nfsstats-'.$app['app_id'].'.rrd';
$options      = '-O qv';
$mib          = 'NET-SNMP-EXTEND-MIB';
$oid	      = '.1.3.6.1.4.1.8072.1.3.2.4'; 
echo ' nfsstat';

$nfsstats = snmp_walk($device, $oid, $options, $mib);
list($total,$null,$getattr,$setattr,$lookup,$access,$read,$write,$create,$mkdir,$remove,$rmdir,$rename,$readdirplus,$fsstat) = explode("\n",$nfsstats);

if(!is_file($rrd_filename))
{
  rrdtool_create(
    $rrd_filename,
    '--step 300
    DS:total:GAUGE:600:0:U
    DS:null:GAUGE:600:0:U
    DS:getattr:GAUGE:600:0:U
    DS:setattr:GAUGE:600:0:U
    DS:lookup:GAUGE:600:0:U
    DS:access:GAUGE:600:0:U
    DS:read:GAUGE:600:0:U
    DS:write:GAUGE:600:0:U
    DS:create:GAUGE:600:0:U
    DS:mkdir:GAUGE:600:0:U
    DS:remove:GAUGE:600:0:U
    DS:rmdir:GAUGE:600:0:U
    DS:rename:GAUGE:600:0:U
    DS:readdirplus:GAUGE:600:0:U
    DS:fsstat:GAUGE:600:0:U
    '.$config['rrd_rra']
  );
}

$fields = array(
  'total'  => $total,
  'null'  => $null,
  'getattr'  => $getattr,
  'setattr'  => $setattr,
  'lookup'  => $lookup,
  'access'  => $access,
  'read'  => $read,
  'write'  => $write,
  'create'  => $create,
  'mkdir'  => $mkdir,
  'remove' => $remove,
  'rmdir' => $rmdir,
  'rename' => $rename,
  'readdirplus' => $readdirplus,
  'fsstat' => $fsstat,
); 

rrdtool_update($rrd_filename, $fields);
$tags = array('name' => 'nfsstats', 'app_id' => $app['app_id']);
influx_update($device,'app',$tags,$fields);
