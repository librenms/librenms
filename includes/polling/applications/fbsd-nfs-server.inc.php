<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'fbsd-nfs-server';
$app_id = $app['app_id'];
$options      = '-o qv';
$mib          = 'NET-SNMP-EXTEND-MIB';
$oid          = 'nsExtendOutLine.13.102.98.115.100.110.102.115.115.101.114.118.101.114';
$nfsserver = snmp_walk($device, $oid, $options, $mib);

list($getattr, $setattr, $lookup, $readlink, $read, $write, $create, $remove, $rename, $link, $symlink,
    $mkdir, $rmdir, $readdir, $rdirplus, $access, $mknod, $fsstat, $fsinfo, $pathconf, $commit, $retfailed,
    $faults, $inprog, $idem, $nonidem, $misses, $writeops, $writerpc, $opsaved) = explode("\n", $nfsserver);

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
    ->addDataset('retfailed', 'derive', 0)
    ->addDataset('faults', 'derive', 0)
    ->addDataset('inprog', 'derive', 0)
    ->addDataset('idem', 'derive', 0)
    ->addDataset('nonidem', 'derive', 0)
    ->addDataset('misses', 'derive', 0)
    ->addDataset('writeops', 'derive', 0)
    ->addDataset('writerpc', 'derive', 0)
    ->addDataset('opsaved', 'derive', 0);

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
