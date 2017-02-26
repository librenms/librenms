<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'fbsd-nfs-server';
$app_id = $app['app_id'];
$options      = '-O qv';
$mib          = 'NET-SNMP-EXTEND-MIB';
$oid          = 'nsExtendOutLine.13.102.98.115.100.110.102.115.115.101.114.118.101.114';
$nfsserver = snmp_walk($device, $oid, $options, $mib);

list( $Getattr, $Setattr, $Lookup, $Readlink, $Read, $Write, $Create, $Remove, $Rename, $Link, $Symlink,
    $Mkdir, $Rmdir, $Readdir, $RdirPlus, $Access, $Mknod, $Fsstat, $Fsinfo, $PathConf, $Commit, $RetFailed,
    $Faults, $Inprog, $Idem, $Nonidem, $Misses, $WriteOps, $WriteRPC, $Opsaved ) = explode("\n", $nfsserver);

$rrd_name = array('app', $name, $app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('Getattr', 'DERIVE', 0)
    ->addDataset('Setattr', 'DERIVE', 0)
    ->addDataset('Lookup', 'DERIVE', 0)
    ->addDataset('Readlink', 'DERIVE', 0)
    ->addDataset('Read', 'DERIVE', 0)
    ->addDataset('Write', 'DERIVE', 0)
    ->addDataset('Create', 'DERIVE', 0)
    ->addDataset('Remove', 'DERIVE', 0)
    ->addDataset('Rename', 'DERIVE', 0)
    ->addDataset('Link', 'DERIVE', 0)
    ->addDataset('Symlink', 'DERIVE', 0)
    ->addDataset('Mkdir', 'DERIVE', 0)
    ->addDataset('Rmdir', 'DERIVE', 0)
    ->addDataset('Readdir', 'DERIVE', 0)
    ->addDataset('RdirPlus', 'DERIVE', 0)
    ->addDataset('Access', 'DERIVE', 0)
    ->addDataset('Mknod', 'DERIVE', 0)
    ->addDataset('Fsstat', 'DERIVE', 0)
    ->addDataset('Fsinfo', 'DERIVE', 0)
    ->addDataset('PathConf', 'DERIVE', 0)
    ->addDataset('Commit', 'DERIVE', 0)
    ->addDataset('RetFailed', 'DERIVE', 0)
    ->addDataset('Faults', 'DERIVE', 0)
    ->addDataset('Inprog', 'DERIVE', 0)
    ->addDataset('Idem', 'DERIVE', 0)
    ->addDataset('Nonidem', 'DERIVE', 0)
    ->addDataset('Misses', 'DERIVE', 0)
    ->addDataset('WriteOps', 'DERIVE', 0)
    ->addDataset('WriteRPC', 'DERIVE', 0)
    ->addDataset('Opsaved', 'DERIVE', 0);

$fields = array(
    'Getattr' => $Getattr,
    'Setattr' => $Setattr,
    'Lookup' => $Lookup,
    'Readlink' => $Readlink,
    'Read' => $Read,
    'Write' => $Write,
    'Create' => $Create,
    'Remove' => $Remove,
    'Rename' => $Rename,
    'Link' => $Link,
    'Symlink' => $Symlink,
    'Mkdir' => $Mkdir,
    'Rmdir' => $Rmdir,
    'Readdir' => $Readdir,
    'RdirPlus' => $RdirPlus,
    'Access' => $Access,
    'Mknod' => $Mknod,
    'Fsstat' => $Fsstat,
    'Fsinfo' => $Fsinfo,
    'PathConf' => $PathConf,
    'Commit' => $Commit,
    'RetFailed' => $RetFailed,
    'Faults' => $Faults,
    'Inprog' => $Inprog,
    'Idem' => $Idem,
    'Nonidem' => $Nonidem,
    'Misses' => $Misses,
    'WriteOps' => $WriteOps,
    'WriteRPC' => $WriteRPC,
    'Opsaved' => $Opsaved,
);

$tags = array('name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name);
data_update($device, 'app', $tags, $fields);
