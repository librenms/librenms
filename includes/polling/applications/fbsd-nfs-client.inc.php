<?php
$name = 'fbsd-nfs-client';
$app_id = $app['app_id'];

use LibreNMS\RRD\RrdDefinition;

$options      = '-o qv';
$mib          = 'NET-SNMP-EXTEND-MIB';
$oid          = 'nsExtendOutLine.13.102.98.115.100.110.102.115.99.108.105.101.110.116';
$nfsclient = snmp_walk($device, $oid, $options, $mib);

list($getattr, $setattr, $lookup, $readlink, $read, $write, $create, $remove, $rename, $link, $symlink, $mkdir, $rmdir,
    $readdir, $rdirplus, $access, $mknod, $fsstat, $fsinfo, $pathconf, $commit, $timedout, $invalid, $xreplies, $retries,
    $requests, $attrhits, $attrmisses, $lkuphits, $lkupmisses, $biorhits, $biormisses, $biowhits, $biowmisses, $biorlhits,
    $biorlmisses, $biodhits, $biodmisses, $direhits, $diremisses, $accshits, $accsmisses) = explode("\n", $nfsclient);

$rrd_name = array('app', $name, $app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('getattr', 'derive', 0)
    ->addDataset('setattr', 'derive', 0)
    ->addDataset('lookup', 'derive', 0)
    ->addDataset('readlink', 'derive', 0)
    ->addDataset('read', 'derive', 0)
    ->addDataset('write', 'derive', 0)
    ->addDataset('create', 'derive', 0)
    ->addDataset('remove', 'derive', 0)
    ->addDataset('rename', 'derive', 0)
    ->addDataset('link', 'derive', 0)
    ->addDataset('symlink', 'derive', 0)
    ->addDataset('mkdir', 'derive', 0)
    ->addDataset('rmdir', 'derive', 0)
    ->addDataset('readdir', 'derive', 0)
    ->addDataset('rdirplus', 'derive', 0)
    ->addDataset('access', 'derive', 0)
    ->addDataset('mknod', 'derive', 0)
    ->addDataset('fsstat', 'derive', 0)
    ->addDataset('fsinfo', 'derive', 0)
    ->addDataset('pathconf', 'derive', 0)
    ->addDataset('commit', 'derive', 0)
    ->addDataset('timedout', 'derive', 0)
    ->addDataset('invalid', 'derive', 0)
    ->addDataset('xreplies', 'derive', 0)
    ->addDataset('retries', 'derive', 0)
    ->addDataset('requests', 'derive', 0)
    ->addDataset('attrhits', 'derive', 0)
    ->addDataset('attrmisses', 'derive', 0)
    ->addDataset('lkuphits', 'derive', 0)
    ->addDataset('lkupmisses', 'derive', 0)
    ->addDataset('biorhits', 'derive', 0)
    ->addDataset('biormisses', 'derive', 0)
    ->addDataset('biowhits', 'derive', 0)
    ->addDataset('biowmisses', 'derive', 0)
    ->addDataset('biorlhits', 'derive', 0)
    ->addDataset('biorlmisses', 'derive', 0)
    ->addDataset('biodhits', 'derive', 0)
    ->addDataset('biodmisses', 'derive', 0)
    ->addDataset('direhits', 'derive', 0)
    ->addDataset('diremisses', 'derive', 0)
    ->addDataset('accshits', 'derive', 0)
    ->addDataset('accsmisses', 'derive', 0);

$fields = array(
    'getattr' => $getattr,
    'setattr' => $setattr,
    'lookup' => $lookup,
    'readlink' => $readlink,
    'read' => $read,
    'write' => $write,
    'create' => $create,
    'remove' => $remove,
    'rename' => $rename,
    'link' => $link,
    'symlink' => $symlink,
    'mkdir' => $mkdir,
    'rmdir' => $rmdir,
    'readdir' => $readdir,
    'rdirplus' => $rdirplus,
    'access' => $access,
    'mknod' => $mknod,
    'fsstat' => $fsstat,
    'fsinfo' => $fsinfo,
    'pathconf' => $pathconf,
    'commit' => $commit,
    'timedout' => $timedout,
    'invalid' => $invalid,
    'xreplies' => $xreplies,
    'retries' => $retries,
    'requests' => $requests,
    'attrhits' => $attrhits,
    'attrmisses' => $attrmisses,
    'lkuphits' => $lkuphits,
    'lkupmisses' => $lkupmisses,
    'biorhits' => $biorhits,
    'biormisses' => $biormisses,
    'biowhits' => $biowhits,
    'biowmisses' => $biowmisses,
    'biorlhits' => $biorlhits,
    'biorlmisses' => $biorlmisses,
    'biodhits' => $biodhits,
    'biodmisses' => $biodmisses,
    'direhits' => $direhits,
    'diremisses' => $diremisses,
    'accshits' => $accshits,
    'accsmisses' => $accsmisses,
);

$tags = array('name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name);
data_update($device, 'app', $tags, $fields);
