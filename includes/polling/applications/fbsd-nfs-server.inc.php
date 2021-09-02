<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppParsingFailedException;
use LibreNMS\RRD\RrdDefinition;

$name = 'fbsd-nfs-server';
$app_id = $app['app_id'];

echo $name;

try {
    $nfs = json_app_get($device, 'fbsdnfsserver');
} catch (JsonAppParsingFailedException $e) {
    // Legacy script, build compatible array
    $legacy = $e->getOutput();

    $nfs = [
        'data' => [],
    ];
    [$nfs['data']['Getattr'], $nfs['data']['Setattr'], $nfs['data']['Lookup'], $nfs['data']['Readlink'],
         $nfs['data']['Read'], $nfs['data']['Write'], $nfs['data']['Create'], $nfs['data']['Remove'],
         $nfs['data']['Rename'], $nfs['data']['Link'], $nfs['data']['Symlink'], $nfs['data']['Mkdir'],
         $nfs['data']['Rmdir'], $nfs['data']['Readdir'], $nfs['data']['RdirPlus'], $nfs['data']['Access'],
         $nfs['data']['Mknod'], $nfs['data']['Fsstat'], $nfs['data']['Fsinfo'], $nfs['data']['PathConf'],
         $nfs['data']['Commit'], $nfs['data']['RetFailed'], $nfs['data']['Faults'], $nfs['data']['Inprog'],
         $nfs['data']['Idem'], $nfs['data']['Nonidem'], $nfs['data']['Misses'], $nfs['data']['WriteOps'],
         $nfs['data']['WriteRPC'], $nfs['data']['Opsaved']] = explode("\n", $legacy);
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$rrd_name = ['app', $name, $app_id];
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

$fields = [
    'getattr' => $nfs['data']['Getattr'],
    'setattr' => $nfs['data']['Setattr'],
    'lookup' => $nfs['data']['Lookup'],
    'readlink' => $nfs['data']['Readlink'],
    'read' => $nfs['data']['Read'],
    'write' => $nfs['data']['Write'],
    'create' => $nfs['data']['Create'],
    'remove' => $nfs['data']['Remove'],
    'rename' => $nfs['data']['Rename'],
    'link' => $nfs['data']['Link'],
    'symlink' => $nfs['data']['Symlink'],
    'mkdir' => $nfs['data']['Mkdir'],
    'rmdir' => $nfs['data']['Rmdir'],
    'readdir' => $nfs['data']['Readdir'],
    'rdirplus' => $nfs['data']['RdirPlus'],
    'access' => $nfs['data']['Access'],
    'mknod' => $nfs['data']['Mknod'],
    'fsstat' => $nfs['data']['Fsstat'],
    'fsinfo' => $nfs['data']['Fsinfo'],
    'pathconf' => $nfs['data']['PathConf'],
    'commit' => $nfs['data']['Commit'],
    'retfailed' => $nfs['data']['RetFailed'],
    'faults' => $nfs['data']['Faults'],
    'inprog' => $nfs['data']['Inprog'],
    'idem' => $nfs['data']['Idem'],
    'nonidem' => $nfs['data']['Nonidem'],
    'misses' => $nfs['data']['Misses'],
    'writeops' => $nfs['data']['WriteOps'],
    'writerpc' => $nfs['data']['WriteRPC'],
    'opsaved' => $nfs['data']['Opsaved'],
];

$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);
update_application($app, 'OK', $nfs['data']);
