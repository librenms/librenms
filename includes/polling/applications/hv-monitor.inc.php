<?php

use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\RRD\RrdDefinition;

$name = 'hv-monitor';
try {
    $return_data = json_app_get($device, 'hv-monitor')['data'];
} catch (JsonAppException $e) {
    echo PHP_EOL . $name . ':' . $e->getCode() . ':' . $e->getMessage() . PHP_EOL;
    update_application($app, $e->getCode() . ':' . $e->getMessage(), []); // Set empty metrics and error message

    return;
}

$app_data['hv'] = $return_data['hv'];

if (! is_array($app_data['VMs'])) {
    $app_data['VMs'] = [];
}

if (! is_array($app_data['VMdisks'])) {
    $app_data['VMdisks'] = [];
}

if (! is_array($app_data['VMifs'])) {
    $app_data['VMdifs'] = [];
}

if (! is_array($app_data['VMstatus'])) {
    $app_data['VMdstatus'] = [];
}

//
// totals graph stuff
//
$rrd_def = RrdDefinition::make();
$ds = [
    'usertime' => 'DDERIVE',
    'pmem' => 'GAUGE',
    'oublk' => 'DERIVE',
    'minflt' => 'DERIVE',
    'pcpu' => 'GAUGE',
    'mem_alloc' => 'GAUGE',
    'nvcsw' => 'DERIVE',
    'snaps' => 'GAUGE',
    'rss' => 'GAUGE',
    'snaps_size' => 'GAUGE',
    'cpus' => 'GAUGE',
    'cow' => 'DERIVE',
    'nivcsw' => 'DERIVE',
    'systime' => 'DDERIVE',
    'vsz' => 'GAUGE',
    'etimes' => 'GAUGE',
    'majflt' => 'GAUGE',
    'inblk' => 'DERIVE',
    'nswap' => 'GAUGE',
    'on' => 'GAUGE',
    'off' => 'GAUGE',
    'off_hard' => 'GAUGE',
    'off_soft' => 'GAUGE',
    'unknown' => 'GAUGE',
    'paused' => 'GAUGE',
    'crashed' => 'GAUGE',
    'blocked' => 'GAUGE',
    'nostate' => 'GAUGE',
    'pmsuspended' => 'GAUGE',
    'rbytes' => 'DERIVE',
    'rtime' => 'DDERIVE',
    'rreqs' => 'DERIVE',
    'wbytes' => 'DERIVE',
    'wtime' => 'DDERIVE',
    'wreqs' => 'DERIVE',
    'disk_alloc' => 'GAUGE',
    'disk_in_use' => 'GAUGE',
    'disk_on_disk' => 'GAUGE',
    'ftime' => 'DDERIVE',
    'freqs' => 'DERIVE',
    'ipkts' => 'DERIVE',
    'ierrs' => 'DERIVE',
    'ibytes' => 'DERIVE',
    'idrop' => 'DERIVE',
    'opkts' => 'DERIVE',
    'oerrs' => 'DERIVE',
    'obytes' => 'DERIVE',
    'odrop' => 'DERIVE',
    'coll' => 'DERIVE',
];

$totals_fields = [];
foreach ($ds as $key => $type) {
    $rrd_def->addDataset($key, $type, 0);
    $totals_fields[$key] = $return_data['totals'][$type];
}

$rrd_name = ['app', $name, $app->app_id];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
app('Datastore')->put($device, 'app', $tags, $totals_fields);

//
// handle each VM
//
$ds = [
    'usertime' => 'DDERIVE',
    'pmem' => 'GAUGE',
    'oublk' => 'DERIVE',
    'minflt' => 'DERIVE',
    'pcpu' => 'GAUGE',
    'mem_alloc' => 'GAUGE',
    'nvcsw' => 'DERIVE',
    'snaps' => 'GAUGE',
    'rss' => 'GAUGE',
    'snaps_size' => 'GAUGE',
    'cpus' => 'GAUGE',
    'cow' => 'DERIVE',
    'nivcsw' => 'DERIVE',
    'systime' => 'DDERIVE',
    'vsz' => 'GAUGE',
    'etimes' => 'GAUGE',
    'majflt' => 'GAUGE',
    'inblk' => 'DERIVE',
    'nswap' => 'GAUGE',
    'status_int' => 'GAUGE',
    'rbytes' => 'DERIVE',
    'rtime' => 'DDERIVE',
    'rreqs' => 'DERIVE',
    'wbytes' => 'DERIVE',
    'wtime' => 'DDERIVE',
    'wreqs' => 'DERIVE',
    'disk_alloc' => 'GAUGE',
    'disk_in_use' => 'GAUGE',
    'disk_on_disk' => 'GAUGE',
    'ftime' => 'DDERIVE',
    'freqs' => 'DERIVE',
    'ipkts' => 'DERIVE',
    'ierrs' => 'DERIVE',
    'ibytes' => 'DERIVE',
    'idrop' => 'DERIVE',
    'opkts' => 'DERIVE',
    'oerrs' => 'DERIVE',
    'obytes' => 'DERIVE',
    'odrop' => 'DERIVE',
    'coll' => 'DERIVE',
];

$vm_rrd_def = RrdDefinition::make();
foreach ($ds as $key => $type) {
    $vm_rrd_def->addDataset($key, $type, 0);
}

$VMs = [];
foreach ($return_data['VMs'] as $vm => $vm_info) {
    $VMs[] = $vm;

    $vm_fields = [];
    foreach ($ds as $key => $_) {
        $vm_fields[$key] = $vm_info[$key] ?? null;
    }

    $rrd_name = ['app', $name, $app->app_id, 'vm', $vm];
    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $vm_rrd_def, 'rrd_name' => $rrd_name];
    app('Datastore')->put($device, 'app', $tags, $vm_fields);
}
sort($VMs);
$app_data['VMs'] = $VMs;

//
// process each disk
//
$disk_rrd_def = RrdDefinition::make()
    ->addDataset('in_use', 'GAUGE', 0)
    ->addDataset('on_disk', 'GAUGE', 0)
    ->addDataset('alloc', 'GAUGE', 0)
    ->addDataset('rbytes', 'DERIVE', 0)
    ->addDataset('rtime', 'DDERIVE', 0)
    ->addDataset('rreqs', 'DERIVE', 0)
    ->addDataset('wbytes', 'DERIVE', 0)
    ->addDataset('wtime', 'DDERIVE', 0)
    ->addDataset('wreqs', 'DERIVE', 0)
    ->addDataset('ftime', 'DDERIVE', 0)
    ->addDataset('freqs', 'DERIVE', 0);

foreach ($VMs as $vm) {
    $vm_disks = [];

    foreach ($return_data['VMs'][$vm]['disks'] as $disk => $disk_info) {
        $vm_disks[] = $disk;

        $disk_fields = [
            'in_use' => $disk_info['in_use'],
            'on_disk' => $disk_info['on_disk'],
            'alloc' => $disk_info['alloc'],
            'rbytes' => $disk_info['rbytes'],
            'rtime' => $disk_info['rtime'],
            'rreqs' => $disk_info['rreqs'],
            'wbytes' => $disk_info['wbytes'],
            'wtime' => $disk_info['wtime'],
            'wreqs' => $disk_info['wreqs'],
            'ftime' => $disk_info['ftime'],
            'freqs' => $disk_info['freqs'],
        ];

        $rrd_name = ['app', $name, $app->app_id, 'vmdisk', $vm, '__-__', $disk];
        $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $disk_rrd_def, 'rrd_name' => $rrd_name];
        app('Datastore')->put($device, 'app', $tags, $disk_fields);
    }
    sort($vm_disks);

    $app_data['VMdisks'][$vm] = $vm_disks;
}

//
// process each if
//
$if_rrd_def = RrdDefinition::make()
    ->addDataset('ipkts', 'DERIVE', 0)
    ->addDataset('ierrs', 'DERIVE', 0)
    ->addDataset('ibytes', 'DERIVE', 0)
    ->addDataset('idrop', 'DERIVE', 0)
    ->addDataset('opkts', 'DERIVE', 0)
    ->addDataset('oerrs', 'DERIVE', 0)
    ->addDataset('obytes', 'DERIVE', 0)
    ->addDataset('odrop', 'DERIVE', 0)
    ->addDataset('coll', 'DERIVE', 0);

foreach ($VMs as $vm) {
    $vm_ifs = [];

    foreach ($return_data['VMs'][$vm]['ifs'] as $vm_if => $if_info) {
        $vm_ifs[$vm_if] = [
            'mac' => $if_info['mac'],
            'parent' => $if_info['parent'],
            'if' => $if_info['if'],
        ];

        $if_fields = [
            'ipkts' => $if_info['ipkts'],
            'ierrs' => $if_info['ierrs'],
            'ibytes' => $if_info['ibytes'],
            'idrop' => $if_info['idrop'],
            'opkts' => $if_info['opkts'],
            'oerrs' => $if_info['oerrs'],
            'obytes' => $if_info['obytes'],
            'odrop' => $if_info['odrop'],
            'coll' => $if_info['coll'],
        ];

        $rrd_name = ['app', $name, $app->app_id, 'vmif', $vm, '__-__', $vm_if];
        $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $if_rrd_def, 'rrd_name' => $rrd_name];
        app('Datastore')->put($device, 'app', $tags, $if_fields);
    }

    $app_data['VMifs'][$vm] = $vm_ifs;
}

//
// all done so update the app metrics and app_data
//
$app->data = $app_data;
unset($return_data['hv']);
update_application($app, 'OK', data_flatten($return_data));
