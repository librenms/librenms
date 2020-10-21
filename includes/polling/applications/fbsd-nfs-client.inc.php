<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppParsingFailedException;
use LibreNMS\RRD\RrdDefinition;

$name = 'fbsd-nfs-client';
$app_id = $app['app_id'];

echo $name;

try {
    $nfs = json_app_get($device, 'fbsdnfsclient', 0);
} catch (JsonAppParsingFailedException $e) {
    // Legacy script, build compatible array
    $legacy = $e->getOutput();

    $nfs = [
        'data' => [],
    ];

    [$nfs['data']['Getattr'], $nfs['data']['Setattr'], $nfs['data']['Lookup'], $nfs['data']['Readlink'],
         $nfs['data']['Read'], $nfs['data']['Write'], $nfs['data']['Create'], $nfs['data']['Remove'], $nfs['data']['Rename'],
         $nfs['data']['Link'], $nfs['data']['Symlink'], $nfs['data']['Mkdir'], $nfs['data']['Rmdir'], $nfs['data']['Readdir'],
         $nfs['data']['RdirPlus'], $nfs['data']['Access'], $nfs['data']['Mknod'], $nfs['data']['Fsstat'], $nfs['data']['Fsinfo'],
         $nfs['data']['PathConf'], $nfs['data']['Commit'], $nfs['data']['TimedOut'], $nfs['data']['Invalid'], $nfs['data']['XReplies'],
         $nfs['data']['Retries'], $nfs['data']['Requests'], $nfs['data']['AttrHits'], $nfs['data']['AttrMisses'], $nfs['data']['LkupHits'],
         $nfs['data']['LkupMisses'], $nfs['data']['BioRHits'], $nfs['data']['BioRMisses'], $nfs['data']['BioWHits'],
         $nfs['data']['BioWMisses'], $nfs['data']['BioRLHits'], $nfs['data']['BioRLMisses'], $nfs['data']['BioDHits'],
         $nfs['data']['BioDMisses'], $nfs['data']['DirEHits'], $nfs['data']['DirEMisses'], $nfs['data']['AccsHits'],
         $nfs['data']['AccsMisses']] = explode("\n", $legacy);
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
    'symlink' => $nfs['data']['SymLink'],
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
    'timedout' => $nfs['data']['Timedout'],
    'invalid' => $nfs['data']['Invalid'],
    'xreplies' => $nfs['data']['XReplies'],
    'retries' => $nfs['data']['Retries'],
    'requests' => $nfs['data']['Requests'],
    'attrhits' => $nfs['data']['AttrHits'],
    'attrmisses' => $nfs['data']['AttrMisses'],
    'lkuphits' => $nfs['data']['LkupHits'],
    'lkupmisses' => $nfs['data']['LkupMisses'],
    'biorhits' => $nfs['data']['BioRHits'],
    'biormisses' => $nfs['data']['BioRMisses'],
    'biowhits' => $nfs['data']['BioWHits'],
    'biowmisses' => $nfs['data']['BioWMisses'],
    'biorlhits' => $nfs['data']['BioRLHits'],
    'biorlmisses' => $nfs['data']['BioRLMisses'],
    'biodhits' => $nfs['data']['BioDHits'],
    'biodmisses' => $nfs['data']['BioDMisses'],
    'direhits' => $nfs['data']['DirEHits'],
    'diremisses' => $nfs['data']['DirEMisses'],
    'accshits' => $nfs['data']['AccsHits'],
    'accsmisses' => $nfs['data']['AccsMisses'],
];

$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);
update_application($app, 'OK', $nfs['data']);
