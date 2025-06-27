<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppParsingFailedException;
use LibreNMS\RRD\RrdDefinition;

$name = 'fbsd-nfs-client';

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

$rrd_name = ['app', $name, $app->app_id];
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
    'getattr' => $nfs['data']['Getattr'] ?? null,
    'setattr' => $nfs['data']['Setattr'] ?? null,
    'lookup' => $nfs['data']['Lookup'] ?? null,
    'readlink' => $nfs['data']['Readlink'] ?? null,
    'read' => $nfs['data']['Read'] ?? null,
    'write' => $nfs['data']['Write'] ?? null,
    'create' => $nfs['data']['Create'] ?? null,
    'remove' => $nfs['data']['Remove'] ?? null,
    'rename' => $nfs['data']['Rename'] ?? null,
    'link' => $nfs['data']['Link'] ?? null,
    'symlink' => $nfs['data']['SymLink'] ?? null,
    'mkdir' => $nfs['data']['Mkdir'] ?? null,
    'rmdir' => $nfs['data']['Rmdir'] ?? null,
    'readdir' => $nfs['data']['Readdir'] ?? null,
    'rdirplus' => $nfs['data']['RdirPlus'] ?? null,
    'access' => $nfs['data']['Access'] ?? null,
    'mknod' => $nfs['data']['Mknod'] ?? null,
    'fsstat' => $nfs['data']['Fsstat'] ?? null,
    'fsinfo' => $nfs['data']['Fsinfo'] ?? null,
    'pathconf' => $nfs['data']['PathConf'] ?? null,
    'commit' => $nfs['data']['Commit'] ?? null,
    'timedout' => $nfs['data']['Timedout'] ?? null,
    'invalid' => $nfs['data']['Invalid'] ?? null,
    'xreplies' => $nfs['data']['XReplies'] ?? null,
    'retries' => $nfs['data']['Retries'] ?? null,
    'requests' => $nfs['data']['Requests'] ?? null,
    'attrhits' => $nfs['data']['AttrHits'] ?? null,
    'attrmisses' => $nfs['data']['AttrMisses'] ?? null,
    'lkuphits' => $nfs['data']['LkupHits'] ?? null,
    'lkupmisses' => $nfs['data']['LkupMisses'] ?? null,
    'biorhits' => $nfs['data']['BioRHits'] ?? null,
    'biormisses' => $nfs['data']['BioRMisses'] ?? null,
    'biowhits' => $nfs['data']['BioWHits'] ?? null,
    'biowmisses' => $nfs['data']['BioWMisses'] ?? null,
    'biorlhits' => $nfs['data']['BioRLHits'] ?? null,
    'biorlmisses' => $nfs['data']['BioRLMisses'] ?? null,
    'biodhits' => $nfs['data']['BioDHits'] ?? null,
    'biodmisses' => $nfs['data']['BioDMisses'] ?? null,
    'direhits' => $nfs['data']['DirEHits'] ?? null,
    'diremisses' => $nfs['data']['DirEMisses'] ?? null,
    'accshits' => $nfs['data']['AccsHits'] ?? null,
    'accsmisses' => $nfs['data']['AccsMisses'] ?? null,
];

$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $fields);
update_application($app, 'OK', $nfs['data']);
