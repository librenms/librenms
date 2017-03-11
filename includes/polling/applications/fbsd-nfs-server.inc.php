<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'fbsd-nfs-server';
$app_id = $app['app_id'];
$options      = '-O qv';
$mib          = 'NET-SNMP-EXTEND-MIB';
$oid          = 'nsExtendOutLine.13.102.98.115.100.110.102.115.115.101.114.118.101.114';
$nfsserver = snmp_walk($device, $oid, $options, $mib);
update_application($app, $nfsserver);

list($getattr, $setattr, $lookup, $readlink, $read, $write, $create, $remove, $rename, $link, $symlink,
    $mkdir, $rmdir, $readdir, $rdirplus, $access, $mknod, $fsstat, $fsinfo, $pathconf, $commit, $retfailed,
    $faults, $inprog, $idem, $nonidem, $misses, $writeops, $writerpc, $opsaved) = explode("\n", $nfsserver);

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
    ->addDataset('retfailed', 'DERIVE', 0)
    ->addDataset('faults', 'DERIVE', 0)
    ->addDataset('inprog', 'DERIVE', 0)
    ->addDataset('idem', 'DERIVE', 0)
    ->addDataset('nonidem', 'DERIVE', 0)
    ->addDataset('misses', 'DERIVE', 0)
    ->addDataset('writeops', 'DERIVE', 0)
    ->addDataset('writerpc', 'DERIVE', 0)
    ->addDataset('opsaved', 'DERIVE', 0);

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
    'retfailed' => $retfailed,
    'faults' => $faults,
    'inprog' => $inprog,
    'idem' => $idem,
    'nonidem' => $nonidem,
    'misses' => $misses,
    'writeops' => $writeops,
    'writerpc' => $writerpc,
    'opsaved' => $opsaved,
);

$tags = array('name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name);
data_update($device, 'app', $tags, $fields);
