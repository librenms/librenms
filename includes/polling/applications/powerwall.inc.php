<?php

use LibreNMS\RRD\RrdDefinition;

//NET-SNMP-EXTEND-MIB::nsExtendOutputFull."powerwall"
$name = 'powerwall';
$app_id = $app['app_id'];

$solaroid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.5.115.111.108.97.114.1';
$solar = snmp_get($device, $solaroid, '-Oqv');
//$solar = str_replace('"', '', $solar);

$loadoid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.4.108.111.97.100.1';
$load = snmp_get($device, $loadoid, '-Oqv');
//$load = str_replace('"', '', $load);

$batteryoid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.7.98.97.116.116.101.114.121.1';
$battery = snmp_get($device, $batteryoid, '-Oqv');
//$battery = str_replace('"', '', $battery);

$gridoid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.4.103.114.105.100.1';
$grid = snmp_get($device, $gridoid, '-Oqv');
//$grid = str_replace('"', '', $grid);

$chargeoid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.6.99.104.97.114.103.101.1';
$charge = snmp_get($device, $chargeoid, '-Oqv');
//$charge = str_replace('"', '', $charge);


echo ' '.$name;


$rrd_name = array('app', $name, $app_id);
$rrd_def = RrdDefinition::make()
    ->addDataset('solar', 'GAUGE')
    ->addDataset('load', 'GAUGE')
    ->addDataset('battery', 'GAUGE')
    ->addDataset('grid', 'GAUGE')
    ->addDataset('charge', 'GAUGE', 0, 100);

$fields = array(
    'solar' => $solar,
    'load' => $load,
    'grid' => $battery,
    'battery' => $grid,
    'charge' => $charge,
);

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
update_application($app, $name, $fields);


