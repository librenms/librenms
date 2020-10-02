<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'squid';
$app_id = $app['app_id'];

$oids = [
    '.1.3.6.1.4.1.3495.1.2.5.1.0',
    '.1.3.6.1.4.1.3495.1.2.5.2.0',
    '.1.3.6.1.4.1.3495.1.2.5.3.0',
    '.1.3.6.1.4.1.3495.1.2.5.4.0',
    '.1.3.6.1.4.1.3495.1.3.1.1.0',
    '.1.3.6.1.4.1.3495.1.3.1.2.0',
    '.1.3.6.1.4.1.3495.1.3.1.3.0',
    '.1.3.6.1.4.1.3495.1.3.1.4.0',
    '.1.3.6.1.4.1.3495.1.3.1.5.0',
    '.1.3.6.1.4.1.3495.1.3.1.6.0',
    '.1.3.6.1.4.1.3495.1.3.1.7.0',
    '.1.3.6.1.4.1.3495.1.3.1.8.0',
    '.1.3.6.1.4.1.3495.1.3.1.9.0',
    '.1.3.6.1.4.1.3495.1.3.1.10.0',
    '.1.3.6.1.4.1.3495.1.3.1.11.0',
    '.1.3.6.1.4.1.3495.1.3.1.12.0',
    '.1.3.6.1.4.1.3495.1.3.1.13.0',
    '.1.3.6.1.4.1.3495.1.3.2.1.1.0',
    '.1.3.6.1.4.1.3495.1.3.2.1.2.0',
    '.1.3.6.1.4.1.3495.1.3.2.1.3.0',
    '.1.3.6.1.4.1.3495.1.3.2.1.4.0',
    '.1.3.6.1.4.1.3495.1.3.2.1.5.0',
    '.1.3.6.1.4.1.3495.1.3.2.1.6.0',
    '.1.3.6.1.4.1.3495.1.3.2.1.7.0',
    '.1.3.6.1.4.1.3495.1.3.2.1.8.0',
    '.1.3.6.1.4.1.3495.1.3.2.1.9.0',
    '.1.3.6.1.4.1.3495.1.3.2.1.10.0',
    '.1.3.6.1.4.1.3495.1.3.2.1.11.0',
    '.1.3.6.1.4.1.3495.1.3.2.1.12.0',
    '.1.3.6.1.4.1.3495.1.3.2.1.13.0',
    '.1.3.6.1.4.1.3495.1.3.2.1.14.0',
    '.1.3.6.1.4.1.3495.1.3.2.1.15.0',
    '.1.3.6.1.4.1.3495.1.3.2.2.1.9.1',
    '.1.3.6.1.4.1.3495.1.3.2.2.1.9.5',
    '.1.3.6.1.4.1.3495.1.3.2.2.1.9.60',
    '.1.3.6.1.4.1.3495.1.3.2.2.1.10.1',
    '.1.3.6.1.4.1.3495.1.3.2.2.1.10.5',
    '.1.3.6.1.4.1.3495.1.3.2.2.1.10.60',
];
$returnedoids = snmp_get_multi_oid($device, $oids);

$memmaxsize = $returnedoids['.1.3.6.1.4.1.3495.1.2.5.1.0'];
$swapmaxsize = $returnedoids['.1.3.6.1.4.1.3495.1.2.5.2.0'];
$swaphighwm = $returnedoids['.1.3.6.1.4.1.3495.1.2.5.3.0'];
$swaplowwm = $returnedoids['.1.3.6.1.4.1.3495.1.2.5.4.0'];
$syspagefaults = $returnedoids['.1.3.6.1.4.1.3495.1.3.1.1.0'];
$sysnumreads = $returnedoids['.1.3.6.1.4.1.3495.1.3.1.2.0'];
$memusage = $returnedoids['.1.3.6.1.4.1.3495.1.3.1.3.0'];
$cputime = $returnedoids['.1.3.6.1.4.1.3495.1.3.1.4.0'];
$cpuusage = $returnedoids['.1.3.6.1.4.1.3495.1.3.1.5.0'];
$maxressize = $returnedoids['.1.3.6.1.4.1.3495.1.3.1.6.0'];
$numobjcount = $returnedoids['.1.3.6.1.4.1.3495.1.3.1.7.0'];
$currentlruexpiration = $returnedoids['.1.3.6.1.4.1.3495.1.3.1.8.0'];
$currentunlinkrequests = $returnedoids['.1.3.6.1.4.1.3495.1.3.1.9.0'];
$currentunusedfdescrcnt = $returnedoids['.1.3.6.1.4.1.3495.1.3.1.10.0'];
$currentresfiledescrcnt = $returnedoids['.1.3.6.1.4.1.3495.1.3.1.11.0'];
$currentfiledescrcnt = $returnedoids['.1.3.6.1.4.1.3495.1.3.1.12.0'];
$currentfiledescrmax = $returnedoids['.1.3.6.1.4.1.3495.1.3.1.13.0'];
$protoclienthttprequests = $returnedoids['.1.3.6.1.4.1.3495.1.3.2.1.1.0'];
$httphits = $returnedoids['.1.3.6.1.4.1.3495.1.3.2.1.2.0'];
$httperrors = $returnedoids['.1.3.6.1.4.1.3495.1.3.2.1.3.0'];
$httpinkb = $returnedoids['.1.3.6.1.4.1.3495.1.3.2.1.4.0'];
$httpoutkb = $returnedoids['.1.3.6.1.4.1.3495.1.3.2.1.5.0'];
$icppktssent = $returnedoids['.1.3.6.1.4.1.3495.1.3.2.1.6.0'];
$icppktsrecv = $returnedoids['.1.3.6.1.4.1.3495.1.3.2.1.7.0'];
$icpkbsent = $returnedoids['.1.3.6.1.4.1.3495.1.3.2.1.8.0'];
$icpkbrecv = $returnedoids['.1.3.6.1.4.1.3495.1.3.2.1.9.0'];
$serverrequests = $returnedoids['.1.3.6.1.4.1.3495.1.3.2.1.10.0'];
$servererrors = $returnedoids['.1.3.6.1.4.1.3495.1.3.2.1.11.0'];
$serverinkb = $returnedoids['.1.3.6.1.4.1.3495.1.3.2.1.12.0'];
$serveroutkb = $returnedoids['.1.3.6.1.4.1.3495.1.3.2.1.13.0'];
$currentswapsize = $returnedoids['.1.3.6.1.4.1.3495.1.3.2.1.14.0'];
$clients = $returnedoids['.1.3.6.1.4.1.3495.1.3.2.1.15.0'];
$requesthitratio1 = $returnedoids['.1.3.6.1.4.1.3495.1.3.2.2.1.9.1'];
$requesthitratio5 = $returnedoids['.1.3.6.1.4.1.3495.1.3.2.2.1.9.5'];
$requesthitratio60 = $returnedoids['.1.3.6.1.4.1.3495.1.3.2.2.1.9.60'];
$requestbyteratio1 = $returnedoids['.1.3.6.1.4.1.3495.1.3.2.2.1.10.1'];
$requestbyteratio5 = $returnedoids['.1.3.6.1.4.1.3495.1.3.2.2.1.10.5'];
$requestbyteratio60 = $returnedoids['.1.3.6.1.4.1.3495.1.3.2.2.1.10.60'];

$rrd_name = ['app', $name, $app_id];

$rrd_def = RrdDefinition::make()
    ->addDataset('memmaxsize', 'GAUGE', 0)
    ->addDataset('swapmaxsize', 'GAUGE', 0)
    ->addDataset('swaphighwm', 'GAUGE', 0)
    ->addDataset('swaplowwm', 'GAUGE', 0)
    ->addDataset('syspagefaults', 'COUNTER', 0)
    ->addDataset('sysnumreads', 'COUNTER', 0)
    ->addDataset('memusage', 'GAUGE', 0)
    ->addDataset('cputime', 'GAUGE', 0)
    ->addDataset('cpuusage', 'GAUGE', 0)
    ->addDataset('maxressize', 'GAUGE', 0)
    ->addDataset('numobjcount', 'GAUGE', 0)
    ->addDataset('curunlinkreq', 'GAUGE', 0)
    ->addDataset('curunusedfdescrcnt', 'GAUGE', 0)
    ->addDataset('curresfiledescrcnt', 'GAUGE', 0)
    ->addDataset('curfiledescrcnt', 'GAUGE', 0)
    ->addDataset('curfiledescrmax', 'GAUGE', 0)
    ->addDataset('protoclienthttpreq', 'COUNTER', 0)
    ->addDataset('httphits', 'COUNTER', 0)
    ->addDataset('httperrors', 'COUNTER', 0)
    ->addDataset('httpinkb', 'COUNTER', 0)
    ->addDataset('httpoutkb', 'COUNTER', 0)
    ->addDataset('icppktssent', 'COUNTER', 0)
    ->addDataset('icppktsrecv', 'COUNTER', 0)
    ->addDataset('icpkbsent', 'COUNTER', 0)
    ->addDataset('icpkbrecv', 'COUNTER', 0)
    ->addDataset('serverrequests', 'COUNTER', 0)
    ->addDataset('servererrors', 'COUNTER', 0)
    ->addDataset('serverinkb', 'COUNTER', 0)
    ->addDataset('serveroutkb', 'COUNTER', 0)
    ->addDataset('currentswapsize', 'GAUGE', 0)
    ->addDataset('clients', 'GAUGE', 0)
    ->addDataset('reqhitratio1', 'GAUGE', 0)
    ->addDataset('reqhitratio5', 'GAUGE', 0)
    ->addDataset('reqhitratio60', 'GAUGE', 0)
    ->addDataset('reqbyteratio1', 'GAUGE', 0)
    ->addDataset('reqbyteratio5', 'GAUGE', 0)
    ->addDataset('reqbyteratio60', 'GAUGE', 0);

$memmaxsize = $memmaxsize * 1000;
$swapmaxsize = $swapmaxsize * 1000;
$swaphighwm = $swaphighwm * 1000;
$swaplowwm = $swaplowwm * 1000;

$fields = [
    'memmaxsize' => $memmaxsize,
    'swapmaxsize' => $swapmaxsize,
    'swaphighwm' => $swaphighwm,
    'swaplowwm' => $swaplowwm,
    'syspagefaults' => $syspagefaults,
    'sysnumreads' => $sysnumreads,
    'memusage' => $memusage,
    'cputime' => $cputime,
    'cpuusage' => $cpuusage,
    'maxressize' => $maxressize,
    'numobjcount' => $numobjcount,
    'curunlinkreq' => $currentunlinkrequests,
    'curunusedfdescrcnt' => $currentunusedfdescrcnt,
    'curresfiledescrcnt' => $currentresfiledescrcnt,
    'curfiledescrcnt' => $currentfiledescrcnt,
    'curfiledescrmax' => $currentfiledescrmax,
    'protoclienthttpreq' => $protoclienthttprequests,
    'httphits' => $httphits,
    'httperrors' => $httperrors,
    'httpinkb' => $httpinkb,
    'httpoutkb' => $httpoutkb,
    'icppktssent' => $icppktssent,
    'icppktsrecv' => $icppktsrecv,
    'icpkbsent' => $icpkbsent,
    'icpkbrecv' => $icpkbrecv,
    'serverrequests' => $serverrequests,
    'servererrors' => $servererrors,
    'serverinkb' => $serverinkb,
    'serveroutkb' => $serveroutkb,
    'currentswapsize' => $currentswapsize,
    'clients' => $clients,
    'reqhitratio1' => $requesthitratio1,
    'reqhitratio5' => $requesthitratio5,
    'reqhitratio60' => $requesthitratio60,
    'reqbyteratio1' => $requestbyteratio1,
    'reqbyteratio5' => $requestbyteratio5,
    'reqbyteratio60' => $requestbyteratio60,
];

$tags = ['name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name];
data_update($device, 'app', $tags, $fields);

$squid_app_status = ($returnedoids == false) ? false : 'Data ok';
update_application($app, $squid_app_status, $fields);
