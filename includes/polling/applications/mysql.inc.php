<?php

// FIXME - this is lame
use LibreNMS\RRD\RrdDefinition;

$name = 'mysql';
$app_id = $app['app_id'];
if (! empty($agent_data['app'][$name])) {
    $mysql = $agent_data['app'][$name];
} else {
    // Polls MySQL  statistics from script via SNMP
    $mysql = snmp_get($device, '.1.3.6.1.4.1.8072.1.3.2.3.1.2.5.109.121.115.113.108', '-Ovq');
}

echo ' mysql';
$metrics = [];

// General Stats
$mapping = [
    'IDBLBSe' => 'cr',
    'IBLFh'   => 'ct',
    'IBLWn'   => 'cu',
    'SRows'   => 'ck',
    'SRange'  => 'cj',
    'SMPs'    => 'ci',
    'SScan'   => 'cl',
    'IBIRd'   => 'ai',
    'IBIWr'   => 'aj',
    'IBILg'   => 'ak',
    'IBIFSc'  => 'ah',
    'IDBRDd'  => 'b2',
    'IDBRId'  => 'b0',
    'IDBRRd'  => 'b3',
    'IDBRUd'  => 'b1',
    'IBRd'    => 'ae',
    'IBCd'    => 'af',
    'IBWr'    => 'ag',
    'TLIe'    => 'b5',
    'TLWd'    => 'b4',
    'IBPse'   => 'aa',
    'IBPDBp'  => 'ac',
    'IBPFe'   => 'ab',
    'IBPMps'  => 'ad',
    'TOC'     => 'bc',
    'OFs'     => 'b7',
    'OTs'     => 'b8',
    'OdTs'    => 'b9',
    'IBSRs'   => 'ay',
    'IBSWs'   => 'ax',
    'IBOWs'   => 'az',
    'QCs'     => 'c1',
    'QCeFy'   => 'bu',
    'MaCs'    => 'bl',
    'MUCs'    => 'bf',
    'ACs'     => 'bd',
    'AdCs'    => 'be',
    'TCd'     => 'bi',
    'Cs'      => 'bn',
    'IBTNx'   => 'a5',
    'KRRs'    => 'a0',
    'KRs'     => 'a1',
    'KWR'     => 'a2',
    'KWs'     => 'a3',
    'QCQICe'  => 'bz',
    'QCHs'    => 'bv',
    'QCIs'    => 'bw',
    'QCNCd'   => 'by',
    'QCLMPs'  => 'bx',
    'CTMPDTs' => 'cn',
    'CTMPTs'  => 'cm',
    'CTMPFs'  => 'co',
    'IBIIs'   => 'au',
    'IBIMRd'  => 'av',
    'IBIMs'   => 'aw',
    'IBILog'  => 'al',
    'IBISc'   => 'am',
    'IBIFLg'  => 'an',
    'IBFBl'   => 'aq',
    'IBIIAo'  => 'ap',
    'IBIAd'   => 'as',
    'IBIAe'   => 'at',
    'SFJn'    => 'cd',
    'SFRJn'   => 'ce',
    'SRe'     => 'cf',
    'SRCk'    => 'cg',
    'SSn'     => 'ch',
    'SQs'     => 'b6',
    'BRd'     => 'cq',
    'BSt'     => 'cp',
    'CDe'     => 'c6',
    'CIt'     => 'c4',
    'CISt'    => 'ca',
    'CLd'     => 'c8',
    'CRe'     => 'c7',
    'CRSt'    => 'cc',
    'CSt'     => 'c5',
    'CUe'     => 'c3',
    'CUMi'    => 'c9',
    'SlLa'    => 'br',
];

$data = explode("\n", $mysql);

$map = [];
foreach ($data as $str) {
    [$key, $value] = explode(':', $str);
    $map[$key] = (float) trim($value);
}

$fields = [];
foreach ($mapping as $k => $v) {
    $fields[$k] = (isset($map[$v]) && $map[$v] >= 0) ? $map[$v] : 'U';
}
$metrics = $fields;

$rrd_name = ['app', $name, $app_id];
$rrd_def = RrdDefinition::make()
    ->addDataset('IDBLBSe', 'GAUGE', 0, 125000000000)
    ->addDataset('IBLFh', 'DERIVE', 0, 125000000000)
    ->addDataset('IBLWn', 'DERIVE', 0, 125000000000)
    ->addDataset('SRows', 'COUNTER', 0, 125000000000)
    ->addDataset('SRange', 'DERIVE', 0, 125000000000)
    ->addDataset('SMPs', 'DERIVE', 0, 125000000000)
    ->addDataset('SScan', 'DERIVE', 0, 125000000000)
    ->addDataset('IBIRd', 'DERIVE', 0, 125000000000)
    ->addDataset('IBIWr', 'DERIVE', 0, 125000000000)
    ->addDataset('IBILg', 'DERIVE', 0, 125000000000)
    ->addDataset('IBIFSc', 'DERIVE', 0, 125000000000)
    ->addDataset('IDBRDd', 'DERIVE', 0, 125000000000)
    ->addDataset('IDBRId', 'DERIVE', 0, 125000000000)
    ->addDataset('IDBRRd', 'DERIVE', 0, 125000000000)
    ->addDataset('IDBRUd', 'DERIVE', 0, 125000000000)
    ->addDataset('IBRd', 'DERIVE', 0, 125000000000)
    ->addDataset('IBCd', 'DERIVE', 0, 125000000000)
    ->addDataset('IBWr', 'DERIVE', 0, 125000000000)
    ->addDataset('TLIe', 'DERIVE', 0, 125000000000)
    ->addDataset('TLWd', 'DERIVE', 0, 125000000000)
    ->addDataset('IBPse', 'GAUGE', 0, 125000000000)
    ->addDataset('IBPDBp', 'GAUGE', 0, 125000000000)
    ->addDataset('IBPFe', 'GAUGE', 0, 125000000000)
    ->addDataset('IBPMps', 'GAUGE', 0, 125000000000)
    ->addDataset('TOC', 'GAUGE', 0, 125000000000)
    ->addDataset('OFs', 'GAUGE', 0, 125000000000)
    ->addDataset('OTs', 'GAUGE', 0, 125000000000)
    ->addDataset('OdTs', 'COUNTER', 0, 125000000000)
    ->addDataset('IBSRs', 'DERIVE', 0, 125000000000)
    ->addDataset('IBSWs', 'DERIVE', 0, 125000000000)
    ->addDataset('IBOWs', 'DERIVE', 0, 125000000000)
    ->addDataset('QCs', 'GAUGE', 0, 125000000000)
    ->addDataset('QCeFy', 'GAUGE', 0, 125000000000)
    ->addDataset('MaCs', 'GAUGE', 0, 125000000000)
    ->addDataset('MUCs', 'GAUGE', 0, 125000000000)
    ->addDataset('ACs', 'DERIVE', 0, 125000000000)
    ->addDataset('AdCs', 'DERIVE', 0, 125000000000)
    ->addDataset('TCd', 'GAUGE', 0, 125000000000)
    ->addDataset('Cs', 'DERIVE', 0, 125000000000)
    ->addDataset('IBTNx', 'DERIVE', 0, 125000000000)
    ->addDataset('KRRs', 'DERIVE', 0, 125000000000)
    ->addDataset('KRs', 'DERIVE', 0, 125000000000)
    ->addDataset('KWR', 'DERIVE', 0, 125000000000)
    ->addDataset('KWs', 'DERIVE', 0, 125000000000)
    ->addDataset('QCQICe', 'DERIVE', 0, 125000000000)
    ->addDataset('QCHs', 'DERIVE', 0, 125000000000)
    ->addDataset('QCIs', 'DERIVE', 0, 125000000000)
    ->addDataset('QCNCd', 'DERIVE', 0, 125000000000)
    ->addDataset('QCLMPs', 'DERIVE', 0, 125000000000)
    ->addDataset('CTMPDTs', 'DERIVE', 0, 125000000000)
    ->addDataset('CTMPTs', 'DERIVE', 0, 125000000000)
    ->addDataset('CTMPFs', 'DERIVE', 0, 125000000000)
    ->addDataset('IBIIs', 'DERIVE', 0, 125000000000)
    ->addDataset('IBIMRd', 'DERIVE', 0, 125000000000)
    ->addDataset('IBIMs', 'DERIVE', 0, 125000000000)
    ->addDataset('IBILog', 'DERIVE', 0, 125000000000)
    ->addDataset('IBISc', 'DERIVE', 0, 125000000000)
    ->addDataset('IBIFLg', 'DERIVE', 0, 125000000000)
    ->addDataset('IBFBl', 'DERIVE', 0, 125000000000)
    ->addDataset('IBIIAo', 'DERIVE', 0, 125000000000)
    ->addDataset('IBIAd', 'DERIVE', 0, 125000000000)
    ->addDataset('IBIAe', 'DERIVE', 0, 125000000000)
    ->addDataset('SFJn', 'DERIVE', 0, 125000000000)
    ->addDataset('SFRJn', 'DERIVE', 0, 125000000000)
    ->addDataset('SRe', 'DERIVE', 0, 125000000000)
    ->addDataset('SRCk', 'DERIVE', 0, 125000000000)
    ->addDataset('SSn', 'DERIVE', 0, 125000000000)
    ->addDataset('SQs', 'DERIVE', 0, 125000000000)
    ->addDataset('BRd', 'DERIVE', 0, 125000000000)
    ->addDataset('BSt', 'DERIVE', 0, 125000000000)
    ->addDataset('CDe', 'DERIVE', 0, 125000000000)
    ->addDataset('CIt', 'DERIVE', 0, 125000000000)
    ->addDataset('CISt', 'DERIVE', 0, 125000000000)
    ->addDataset('CLd', 'DERIVE', 0, 125000000000)
    ->addDataset('CRe', 'DERIVE', 0, 125000000000)
    ->addDataset('CRSt', 'DERIVE', 0, 125000000000)
    ->addDataset('CSt', 'DERIVE', 0, 125000000000)
    ->addDataset('CUe', 'DERIVE', 0, 125000000000)
    ->addDataset('CUMi', 'DERIVE', 0, 125000000000)
    ->addDataset('SlLa', 'GAUGE', 0, 125000000000);

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);

// Process state statistics
$mapping_status = [
    'State_closing_tables'       => 'd2',
    'State_copying_to_tmp_table' => 'd3',
    'State_end'                  => 'd4',
    'State_freeing_items'        => 'd5',
    'State_init'                 => 'd6',
    'State_locked'               => 'd7',
    'State_login'                => 'd8',
    'State_preparing'            => 'd9',
    'State_reading_from_net'     => 'da',
    'State_sending_data'         => 'db',
    'State_sorting_result'       => 'dc',
    'State_statistics'           => 'dd',
    'State_updating'             => 'de',
    'State_writing_to_net'       => 'df',
    'State_none'                 => 'dg',
    'State_other'                => 'dh',
];

$rrd_name = ['app', $name, $app_id, 'status'];
$rrd_def = new RrdDefinition();
// because this sends different names for rrd and compared to other datastores, disable $fields name checks
$rrd_def->disableNameChecking();

$fields = [];
foreach ($mapping_status as $desc => $id) {
    $fields[$desc] = (isset($map[$id]) && $map[$id] >= 0) ? $map[$id] : 'U';
    $rrd_def->addDataset($id, 'GAUGE', 0, 125000000000);
}
$metrics += $fields;
$status = true;
$tags = compact('name', 'app_id', 'status', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
update_application($app, $mysql, $metrics);
