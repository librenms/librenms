<?php
use LibreNMS\RRD\RrdDefinition;

# This is the name that will appear in .Apps. for the host. It will also be the RRD file name
$name = 'powerwall';

$app_id = $app['app_id'];
$options = '-O qv';
$mib = 'NET-SNMP-EXTEND-MIB';

# This is the OID that corresponds to the extend command in snmpd, found with snmptranslate
$oid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.9.112.111.119.101.114.119.97.108.108';

$powerwall = snmp_walk($device, $oid, $options, $mib);

list(

$battery_charge,
$solar_power,
$load_power,
$battery_power,
$site_power,
$solar_exported,
$load_imported,
$battery_imported,
$battery_exported,
$site_imported,
$site_exported,

) = explode("\n", $powerwall);

$rrd_name = [
    'app',
    $name,
    $app_id
];

$rrd_def = RrdDefinition::make()
    ->addDataset('battery-charge', 'GAUGE', 0)
    ->addDataset('solar-power', 'GAUGE')
    ->addDataset('load-power', 'GAUGE', 0)
    ->addDataset('battery-power', 'GAUGE')
    ->addDataset('site-power', 'GAUGE')
    ->addDataset('solar-exported', 'GAUGE')
    ->addDataset('load-imported', 'GAUGE')
    ->addDataset('battery-imported', 'GAUGE')
    ->addDataset('battery-exported', 'GAUGE')
    ->addDataset('site-imported', 'GAUGE')
    ->addDataset('site-exported', 'GAUGE')
;

$fields = [
    'battery-charge' => $battery_charge,
    'solar-power' => $solar_power,
    'load-power' => $load_power,
    'battery-power' => $battery_power,
    'site-power' => $site_power,
    'solar-exported' => $solar_exported,
    'load-imported' => $load_imported,
    'battery-imported' => $battery_imported,
    'battery-exported' => $battery_exported,
    'site-imported' => $site_imported,
    'site-exported' => $site_exported,
];

$tags = [
    'name' => $name,
    'app_id' => $app_id,
    'rrd_def' => $rrd_def,
    'rrd_name' => $rrd_name
];
data_update($device, 'app', $tags, $fields);
update_application($app, $powerwall, $fields);
