<?php
$name = 'fbsd-nfs-client';
$app_id = $app['app_id'];

use LibreNMS\RRD\RrdDefinition;

$options      = '-O qv';
$mib          = 'NET-SNMP-EXTEND-MIB';
$oid          = 'nsExtendOutLine.13.102.98.115.100.110.102.115.99.108.105.101.110.116';
$nfsclient = snmp_walk($device, $oid, $options, $mib);

list($Getattr, $Setattr, $Lookup, $Readlink, $Read, $Write, $Create, $Remove, $Rename, $Link, $Symlink, $Mkdir, $Rmdir,
    $Readdir, $RdirPlus, $Access, $Mknod, $Fsstat, $Fsinfo, $PathConf, $Commit, $TimedOut, $Invalid, $XReplies, $Retries,
    $Requests, $AttrHits, $AttrMisses, $LkupHits, $LkupMisses, $BioRHits, $BioRMisses, $BioWHits, $BioWMisses, $BioRLHits,
    $BioRLMisses, $BioDHits, $BioDMisses, $DirEHits, $DirEMisses, $AccsHits, $AccsMisses) = explode("\n", $nfsclient);

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
    ->addDataset('TimedOut', 'DERIVE', 0)
    ->addDataset('Invalid', 'DERIVE', 0)
    ->addDataset('XReplies', 'DERIVE', 0)
    ->addDataset('Retries', 'DERIVE', 0)
    ->addDataset('Requests', 'DERIVE', 0)
    ->addDataset('AttrHits', 'DERIVE', 0)
    ->addDataset('AttrMisses', 'DERIVE', 0)
    ->addDataset('LkupHits', 'DERIVE', 0)
    ->addDataset('LkupMisses', 'DERIVE', 0)
    ->addDataset('BioRHits', 'DERIVE', 0)
    ->addDataset('BioRMisses', 'DERIVE', 0)
    ->addDataset('BioWHits', 'DERIVE', 0)
    ->addDataset('BioWMisses', 'DERIVE', 0)
    ->addDataset('BioRLHits', 'DERIVE', 0)
    ->addDataset('BioRLMisses', 'DERIVE', 0)
    ->addDataset('BioDHits', 'DERIVE', 0)
    ->addDataset('BioDMisses', 'DERIVE', 0)
    ->addDataset('DirEHits', 'DERIVE', 0)
    ->addDataset('DirEMisses', 'DERIVE', 0)
    ->addDataset('AccsHits', 'DERIVE', 0)
    ->addDataset('AccsMisses', 'DERIVE', 0);

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
    'TimedOut' => $TimedOut,
    'Invalid' => $Invalid,
    'XReplies' => $XReplies,
    'Retries' => $Retries,
    'Requests' => $Requests,
    'AttrHits' => $AttrHits,
    'AttrMisses' => $AttrMisses,
    'LkupHits' => $LkupHits,
    'LkupMisses' => $LkupMisses,
    'BioRHits' => $BioRHits,
    'BioRMisses' => $BioRMisses,
    'BioWHits' => $BioWHits,
    'BioWMisses' => $BioWMisses,
    'BioRLHits' => $BioRLHits,
    'BioRLMisses' => $BioRLMisses,
    'BioDHits' => $BioDHits,
    'BioDMisses' => $BioDMisses,
    'DirEHits' => $DirEHits,
    'DirEMisses' => $DirEMisses,
    'AccsHits' => $AccsHits,
    'AccsMisses' => $AccsMisses,
);

$tags = array('name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name);
data_update($device, 'app', $tags, $fields);
