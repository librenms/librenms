<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'nvidia';
$app_id = $app['app_id'];

$options = '-Oqv';
$oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.6.110.118.105.100.105.97';
$gpus = snmp_walk($device, $oid, $options);

$gpuArray = explode("\n", $gpus);

$rrd_def = RrdDefinition::make()
    ->addDataset('pwr', 'GAUGE', 0)
    ->addDataset('temp', 'GAUGE', 0)
    ->addDataset('sm', 'GAUGE', 0)
    ->addDataset('mem', 'GAUGE', 0)
    ->addDataset('enc', 'GAUGE', 0)
    ->addDataset('dec', 'GAUGE', 0)
    ->addDataset('mclk', 'GAUGE', 0)
    ->addDataset('pclk', 'GAUGE', 0)
    ->addDataset('pviol', 'GAUGE', 0)
    ->addDataset('tviol', 'GAUGE', 0)
    ->addDataset('fb', 'GAUGE', 0)
    ->addDataset('bar1', 'GAUGE', 0)
    ->addDataset('sbecc', 'GAUGE', 0)
    ->addDataset('dbecc', 'GAUGE', 0)
    ->addDataset('pci', 'GAUGE', 0)
    ->addDataset('rxpci', 'GAUGE', 0)
    ->addDataset('txpci', 'GAUGE', 0);

$sm_total = 0;
$metrics = [];
foreach ($gpuArray as $index => $gpu) {
    $stats = explode(',', $gpu);

    if (count($stats) == 19) {
        [$gpu, $pwr, $temp, $memtemp, $sm, $mem, $enc, $dec, $mclk, $pclk, $pviol, $tviol,
        $fb, $bar1, $sbecc, $dbecc, $pci, $rxpci, $txpci] = $stats;
    } else {
        [$gpu, $pwr, $temp, $sm, $mem, $enc, $dec, $mclk, $pclk, $pviol, $tviol,
        $fb, $bar1, $sbecc, $dbecc, $pci, $rxpci, $txpci] = $stats;
    }

    $sm_total += $sm;

    $rrd_name = ['app', $name, $app_id, $index];

    $fields = [
        'pwr' => $pwr,
        'temp' => $temp,
        'sm' => $sm,
        'mem' => $mem,
        'enc' => $enc,
        'dec' => $dec,
        'mclk' => $mclk,
        'pclk' => $pclk,
        'pviol' => $pviol,
        'tviol' => $tviol,
        'fb' => $fb,
        'bar1' => $bar1,
        'sbecc' => $sbecc,
        'dbecc' => $dbecc,
        'pci' => $pci,
        'rxpci' => $rxpci,
        'txpci' => $txpci,
    ];
    $metrics[$index] = $fields;

    $tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
    data_update($device, 'app', $tags, $fields);
}
$sm_average = ($sm_total ? ($sm_total / count($gpuArray)) : 0);

update_application($app, $gpus, $metrics, $sm_average);
