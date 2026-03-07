<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use LibreNMS\Exceptions\JsonAppException;
use LibreNMS\Exceptions\JsonAppParsingFailedException;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\JsonApp;

/** @var \App\Models\Application $app */
/** @var array $device */
$name = 'proxmox';
$vm_ports = [];

echo PHP_EOL;

try {
    $app_data = JsonApp::fetch($name, '2.0.0');
    $status = $app_data->errorString ?: 'OK';
    $pmxcluster = $app_data->data['cluster_name'];
    foreach ($app_data->data['vms'] as $vm) {
        foreach ($vm['ports'] as $port) {
            $vm_ports[] = [
                $vm['id'],
                $port['dev'],
                $port['in'],
                $port['out'],
                $vm['name'],
            ];
        }
    }
} catch (JsonAppParsingFailedException $e) {
    $status = 'OK';
    $pmxlines = explode("\n", Str::chopStart($e->getOutput(), "<<<app-proxmox>>>"));
    $pmxcluster = array_shift($pmxlines);
    foreach ($pmxlines as $pmxline) {
        $vm_ports[] = explode('/', $pmxline, 5);
    }
} catch (JsonAppException $e) {
    Log::error($e->getMessage());

    return;
}

$metrics = [];
foreach ($vm_ports as $port) {
    [$vmid, $vmport, $vmpin, $vmpout, $vmdesc] = $port;
    echo "Proxmox ($pmxcluster): $vmdesc: $vmpin/$vmpout/$vmport\n";

    $rrd_def = RrdDefinition::make()
        ->addDataset('INOCTETS', 'DERIVE', 0, 12500000000)
        ->addDataset('OUTOCTETS', 'DERIVE', 0, 12500000000);
    $fields = [
        'INOCTETS' => $vmpin,
        'OUTOCTETS' => $vmpout,
    ];

    $proxmox_metric_prefix = "pmxcluster{$pmxcluster}_vmid{$vmid}_vmport$vmport";
    $metrics[$proxmox_metric_prefix] = $fields;
    $tags = [
        'name' => $name,
        'app_id' => $app->app_id,
        'pmxcluster' => $pmxcluster,
        'vmid' => $vmid,
        'vmport' => $vmport,
        'rrd_proxmox_name' => [
            'pmxcluster' => $pmxcluster,
            'vmid' => $vmid,
            'vmport' => $vmport,
        ],
        'rrd_def' => $rrd_def,
    ];
    app('Datastore')->put($device, 'app', $tags, $fields);

    DB::table('proxmox_ports')->upsert([
        'vm_id' => $vmid,
        'port' => $vmport,
        'last_seen' => Carbon::now(),
    ], ['vm_id', 'port']);
}

update_application($app, 'Ports: ' . count($vm_ports), $metrics, $status);

unset($pmxlines, $pmxcluster, $metrics, $app_data, $status);
