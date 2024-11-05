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
$rrd_def = RrdDefinition::make()
    ->addDataset('usertime', 'DDERIVE', 0)
    ->addDataset('pmem', 'GAUGE', 0)
    ->addDataset('oublk', 'DERIVE', 0)
    ->addDataset('minflt', 'DERIVE', 0)
    ->addDataset('pcpu', 'GAUGE', 0)
    ->addDataset('mem_alloc', 'GAUGE', 0)
    ->addDataset('nvcsw', 'DERIVE', 0)
    ->addDataset('snaps', 'GAUGE', 0)
    ->addDataset('rss', 'GAUGE', 0)
    ->addDataset('snaps_size', 'GAUGE', 0)
    ->addDataset('cpus', 'GAUGE', 0)
    ->addDataset('cow', 'DERIVE', 0)
    ->addDataset('nivcsw', 'DERIVE', 0)
    ->addDataset('systime', 'DDERIVE', 0)
    ->addDataset('vsz', 'GAUGE', 0)
    ->addDataset('etimes', 'GAUGE', 0)
    ->addDataset('majflt', 'GAUGE', 0)
    ->addDataset('inblk', 'DERIVE', 0)
    ->addDataset('nswap', 'GAUGE', 0)
    ->addDataset('on', 'GAUGE', 0)
    ->addDataset('off', 'GAUGE', 0)
    ->addDataset('off_hard', 'GAUGE', 0)
    ->addDataset('off_soft', 'GAUGE', 0)
    ->addDataset('unknown', 'GAUGE', 0)
    ->addDataset('paused', 'GAUGE', 0)
    ->addDataset('crashed', 'GAUGE', 0)
    ->addDataset('blocked', 'GAUGE', 0)
    ->addDataset('nostate', 'GAUGE', 0)
    ->addDataset('pmsuspended', 'GAUGE', 0)
    ->addDataset('rbytes', 'DERIVE', 0)
    ->addDataset('rtime', 'DDERIVE', 0)
    ->addDataset('rreqs', 'DERIVE', 0)
    ->addDataset('wbytes', 'DERIVE', 0)
    ->addDataset('wtime', 'DDERIVE', 0)
    ->addDataset('wreqs', 'DERIVE', 0)
    ->addDataset('disk_alloc', 'GAUGE', 0)
    ->addDataset('disk_in_use', 'GAUGE', 0)
    ->addDataset('disk_on_disk', 'GAUGE', 0)
    ->addDataset('ftime', 'DDERIVE', 0)
    ->addDataset('freqs', 'DERIVE', 0)
    ->addDataset('ipkts', 'DERIVE', 0)
    ->addDataset('ierrs', 'DERIVE', 0)
    ->addDataset('ibytes', 'DERIVE', 0)
    ->addDataset('idrop', 'DERIVE', 0)
    ->addDataset('opkts', 'DERIVE', 0)
    ->addDataset('oerrs', 'DERIVE', 0)
    ->addDataset('obytes', 'DERIVE', 0)
    ->addDataset('odrop', 'DERIVE', 0)
    ->addDataset('coll', 'DERIVE', 0);

$totals_fields = [
    'usertime' => $return_data['totals']['usertime'],
    'pmem' => $return_data['totals']['pmem'],
    'oublk' => $return_data['totals']['oublk'],
    'minflt' => $return_data['totals']['minflt'],
    'pcpu' => $return_data['totals']['pcpu'],
    'mem_alloc' => $return_data['totals']['mem_alloc'],
    'nvcsw' => $return_data['totals']['nvcsw'],
    'snaps' => $return_data['totals']['snaps'],
    'rss' => $return_data['totals']['rss'],
    'snaps_size' => $return_data['totals']['snaps_size'],
    'cpus' => $return_data['totals']['cpus'],
    'cow' => $return_data['totals']['cow'],
    'nivcsw' => $return_data['totals']['nivcsw'],
    'systime' => $return_data['totals']['systime'],
    'vsz' => $return_data['totals']['vsz'],
    'etimes' => $return_data['totals']['etimes'],
    'majflt' => $return_data['totals']['majflt'],
    'inblk' => $return_data['totals']['inblk'],
    'nswap' => $return_data['totals']['nswap'],
    'on' => $return_data['totals']['on'],
    'off' => $return_data['totals']['off'],
    'off_hard' => $return_data['totals']['off_hard'],
    'off_soft' => $return_data['totals']['off_soft'],
    'unknown' => $return_data['totals']['unknown'],
    'paused' => $return_data['totals']['paused'],
    'crashed' => $return_data['totals']['crashed'],
    'blocked' => $return_data['totals']['blocked'],
    'nostate' => $return_data['totals']['nostate'],
    'pmsuspended' => $return_data['totals']['pmsuspended'],
    'rbytes' => $return_data['totals']['rbytes'],
    'rtime' => $return_data['totals']['rtime'],
    'rreqs' => $return_data['totals']['rreqs'],
    'wbytes' => $return_data['totals']['wbytes'],
    'wtime' => $return_data['totals']['wtime'],
    'wreqs' => $return_data['totals']['wreqs'],
    'disk_alloc' => $return_data['totals']['disk_alloc'],
    'disk_in_use' => $return_data['totals']['disk_in_use'],
    'disk_on_disk' => $return_data['totals']['disk_on_disk'],
    'ftime' => $return_data['totals']['ftime'],
    'freqs' => $return_data['totals']['freqs'],
    'ipkts' => $return_data['totals']['ipkts'],
    'ierrs' => $return_data['totals']['ierrs'],
    'ibytes' => $return_data['totals']['ibytes'],
    'idrop' => $return_data['totals']['idrop'],
    'opkts' => $return_data['totals']['opkts'],
    'oerrs' => $return_data['totals']['oerrs'],
    'obytes' => $return_data['totals']['obytes'],
    'odrop' => $return_data['totals']['odrop'],
    'coll' => $return_data['totals']['coll'],
];

$rrd_name = ['app', $name, $app->app_id];
$tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $totals_fields);

//
// handle each VM
//
$vm_rrd_def = RrdDefinition::make()
    ->addDataset('usertime', 'DDERIVE', 0)
    ->addDataset('pmem', 'GAUGE', 0)
    ->addDataset('oublk', 'DERIVE', 0)
    ->addDataset('minflt', 'DERIVE', 0)
    ->addDataset('pcpu', 'GAUGE', 0)
    ->addDataset('mem_alloc', 'GAUGE', 0)
    ->addDataset('nvcsw', 'DERIVE', 0)
    ->addDataset('snaps', 'GAUGE', 0)
    ->addDataset('rss', 'GAUGE', 0)
    ->addDataset('snaps_size', 'GAUGE', 0)
    ->addDataset('cpus', 'GAUGE', 0)
    ->addDataset('cow', 'DERIVE', 0)
    ->addDataset('nivcsw', 'DERIVE', 0)
    ->addDataset('systime', 'DDERIVE', 0)
    ->addDataset('vsz', 'GAUGE', 0)
    ->addDataset('etimes', 'GAUGE', 0)
    ->addDataset('majflt', 'GAUGE', 0)
    ->addDataset('inblk', 'DERIVE', 0)
    ->addDataset('nswap', 'GAUGE', 0)
    ->addDataset('status_int', 'GAUGE', 0)
    ->addDataset('rbytes', 'DERIVE', 0)
    ->addDataset('rtime', 'DDERIVE', 0)
    ->addDataset('rreqs', 'DERIVE', 0)
    ->addDataset('wbytes', 'DERIVE', 0)
    ->addDataset('wtime', 'DDERIVE', 0)
    ->addDataset('wreqs', 'DERIVE', 0)
    ->addDataset('disk_alloc', 'GAUGE', 0)
    ->addDataset('disk_in_use', 'GAUGE', 0)
    ->addDataset('disk_on_disk', 'GAUGE', 0)
    ->addDataset('ftime', 'DDERIVE', 0)
    ->addDataset('freqs', 'DERIVE', 0)
    ->addDataset('ipkts', 'DERIVE', 0)
    ->addDataset('ierrs', 'DERIVE', 0)
    ->addDataset('ibytes', 'DERIVE', 0)
    ->addDataset('idrop', 'DERIVE', 0)
    ->addDataset('opkts', 'DERIVE', 0)
    ->addDataset('oerrs', 'DERIVE', 0)
    ->addDataset('obytes', 'DERIVE', 0)
    ->addDataset('odrop', 'DERIVE', 0)
    ->addDataset('coll', 'DERIVE', 0);

$VMs = [];
foreach ($return_data['VMs'] as $vm => $vm_info) {
    $VMs[] = $vm;

    $vm_fields = [
        'usertime' => $vm_info['usertime'],
        'pmem' => $vm_info['pmem'],
        'oublk' => $vm_info['oublk'],
        'minflt' => $vm_info['minflt'],
        'pcpu' => $vm_info['pcpu'],
        'mem_alloc' => $vm_info['mem_alloc'],
        'nvcsw' => $vm_info['nvcsw'],
        'snaps' => $vm_info['snaps'],
        'rss' => $vm_info['rss'],
        'snaps_size' => $vm_info['snaps_size'],
        'cpus' => $vm_info['cpus'],
        'cow' => $vm_info['cow'],
        'nivcsw' => $vm_info['nivcsw'],
        'systime' => $vm_info['systime'],
        'vsz' => $vm_info['vsz'],
        'etimes' => $vm_info['etimes'],
        'majflt' => $vm_info['majflt'],
        'inblk' => $vm_info['inblk'],
        'nswap' => $vm_info['nswap'],
        'status_int' => $vm_info['status_int'],
        'rbytes' => $vm_info['rbytes'],
        'rtime' => $vm_info['rtime'],
        'rreqs' => $vm_info['rreqs'],
        'wbytes' => $vm_info['wbytes'],
        'wtime' => $vm_info['wtime'],
        'wreqs' => $vm_info['wreqs'],
        'disk_alloc' => $vm_info['disk_alloc'],
        'disk_in_use' => $vm_info['disk_in_use'],
        'disk_on_disk' => $vm_info['disk_on_disk'],
        'ftime' => $vm_info['ftime'],
        'freqs' => $vm_info['freqs'],
        'ipkts' => $vm_info['ipkts'],
        'ierrs' => $vm_info['ierrs'],
        'ibytes' => $vm_info['ibytes'],
        'idrop' => $vm_info['idrop'],
        'opkts' => $vm_info['opkts'],
        'oerrs' => $vm_info['oerrs'],
        'obytes' => $vm_info['obytes'],
        'odrop' => $vm_info['odrop'],
        'coll' => $vm_info['coll'],
    ];

    $rrd_name = ['app', $name, $app->app_id, 'vm', $vm];
    $tags = ['name' => $name, 'app_id' => $app->app_id, 'rrd_def' => $vm_rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $vm_fields);
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
        data_update($device, 'app', $tags, $disk_fields);
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
        data_update($device, 'app', $tags, $if_fields);
    }

    $app_data['VMifs'][$vm] = $vm_ifs;
}

//
// all done so update the app metrics and app_data
//
$app->data = $app_data;
unset($return_data['hv']);
update_application($app, 'OK', data_flatten($return_data));
