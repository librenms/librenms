<?php
$name = 'fbsd-nfs-client';
$app_id = $app['app_id'];

use LibreNMS\RRD\RrdDefinition;

$options      = '-O qv';
$mib          = 'NET-SNMP-EXTEND-MIB';
$oid          = 'nsExtendOutLine.13.102.98.115.100.110.102.115.99.108.105.101.110.116';
$nfsclient = snmp_walk($device, $oid, $options, $mib);
update_application($app, $nfsclient);

list($getattr, $setattr, $lookup, $readlink, $read, $write, $create, $remove, $rename, $link, $symlink, $mkdir, $rmdir,
    $readdir, $rdirplus, $access, $mknod, $fsstat, $fsinfo, $pathconf, $commit, $timedout, $invalid, $xreplies, $retries,
    $requests, $attrhits, $attrmisses, $lkuphits, $lkupmisses, $biorhits, $biormisses, $biowhits, $biowmisses, $biorlhits,
    $biorlmisses, $biodhits, $biodmisses, $direhits, $diremisses, $accshits, $accsmisses) = explode("\n", $nfsclient);

$rrd_name = array('app', $name, $app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('getattr', 'DERIVE', 0)
    ->addDataset('setattr', 'DERIVE', 0)
    ->addDataset('lookup', 'DERIVE', 0)
    ->addDataset('readlink', 'DERIVE', 0)
    ->addDataset('read', 'DERIVE', 0)
    ->addDataset('write', 'DERIVE', 0)
    ->addDataset('create', 'DERIVE', 0)
    ->addDataset('remove', 'DERIVE', 0)
    ->addDataset('rename', 'DERIVE', 0)
    ->addDataset('link', 'DERIVE', 0)
    ->addDataset('symlink', 'DERIVE', 0)
    ->addDataset('mkdir', 'DERIVE', 0)
    ->addDataset('rmdir', 'DERIVE', 0)
    ->addDataset('readdir', 'DERIVE', 0)
    ->addDataset('rdirplus', 'DERIVE', 0)
    ->addDataset('access', 'DERIVE', 0)
    ->addDataset('mknod', 'DERIVE', 0)
    ->addDataset('fsstat', 'DERIVE', 0)
    ->addDataset('fsinfo', 'DERIVE', 0)
    ->addDataset('pathconf', 'DERIVE', 0)
    ->addDataset('commit', 'DERIVE', 0)
    ->addDataset('timedout', 'DERIVE', 0)
    ->addDataset('invalid', 'DERIVE', 0)
    ->addDataset('xreplies', 'DERIVE', 0)
    ->addDataset('retries', 'DERIVE', 0)
    ->addDataset('requests', 'DERIVE', 0)
    ->addDataset('attrhits', 'DERIVE', 0)
    ->addDataset('attrmisses', 'DERIVE', 0)
    ->addDataset('lkuphits', 'DERIVE', 0)
    ->addDataset('lkupmisses', 'DERIVE', 0)
    ->addDataset('biorhits', 'DERIVE', 0)
    ->addDataset('biormisses', 'DERIVE', 0)
    ->addDataset('biowhits', 'DERIVE', 0)
    ->addDataset('biowmisses', 'DERIVE', 0)
    ->addDataset('biorlhits', 'DERIVE', 0)
    ->addDataset('biorlmisses', 'DERIVE', 0)
    ->addDataset('biodhits', 'DERIVE', 0)
    ->addDataset('biodmisses', 'DERIVE', 0)
    ->addDataset('direhits', 'DERIVE', 0)
    ->addDataset('diremisses', 'DERIVE', 0)
    ->addDataset('accshits', 'DERIVE', 0)
    ->addDataset('accsmisses', 'DERIVE', 0);

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
