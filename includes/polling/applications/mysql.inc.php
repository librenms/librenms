<?php

// FIXME - this is lame
$name = 'mysql';
$app_id = $app['app_id'];
if (!empty($agent_data['app'][$name])) {
    $mysql = $agent_data['app'][$name];
} else {
    // Polls MySQL  statistics from script via SNMP
    $mysql_cmd  = $config['snmpget'].' -m NET-SNMP-EXTEND-MIB -O qv '.snmp_gen_auth($device).' '.$device['hostname'].':'.$device['port'];
    $mysql_cmd .= ' nsExtendOutputFull.5.109.121.115.113.108';
    $mysql      = shell_exec($mysql_cmd);
}

echo ' mysql';

// General Stats
$mapping = array(
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
);

$data = explode("\n", $mysql);

$map = array();
foreach ($data as $str) {
    list($key, $value) = explode(':', $str);
    $map[$key]         = (float) trim($value);
}

$fields = array();
foreach ($mapping as $k => $v) {
    $fields[$k] = isset($map[$v]) ? $map[$v] : (-1);
}

$rrd_name = array('app', $name, $app_id);
$rrd_def = array(
    'DS:IDBLBSe:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:IBLFh:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:IBLWn:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:SRows:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:SRange:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:SMPs:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:SScan:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:IBIRd:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:IBIWr:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:IBILg:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:IBIFSc:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:IDBRDd:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:IDBRId:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:IDBRRd:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:IDBRUd:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:IBRd:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:IBCd:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:IBWr:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:TLIe:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:TLWd:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:IBPse:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:IBPDBp:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:IBPFe:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:IBPMps:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:TOC:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:OFs:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:OTs:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:OdTs:COUNTER:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:IBSRs:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:IBSWs:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:IBOWs:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:QCs:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:QCeFy:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:MaCs:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:MUCs:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:ACs:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:AdCs:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:TCd:GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:Cs:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:IBTNx:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:KRRs:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:KRs:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:KWR:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:KWs:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:QCQICe:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:QCHs:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:QCIs:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:QCNCd:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:QCLMPs:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:CTMPDTs:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:CTMPTs:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:CTMPFs:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:IBIIs:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:IBIMRd:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:IBIMs:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:IBILog:DERIVE:602:0:125000000000',
    'DS:IBISc:DERIVE:602:0:125000000000',
    'DS:IBIFLg:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:IBFBl:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:IBIIAo:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:IBIAd:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:IBIAe:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:SFJn:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:SFRJn:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:SRe:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:SRCk:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:SSn:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:SQs:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:BRd:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:BSt:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:CDe:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:CIt:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:CISt:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:CLd:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:CRe:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:CRSt:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:CSt:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:CUe:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000',
    'DS:CUMi:DERIVE:'.$config['rrd']['heartbeat'].':0:125000000000'
);

$tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);

// Process state statistics
$mapping_status = array(
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
);

$rrd_name = array('app', $name, $app_id, 'status');
$rrd_def = array();
unset($fields);
foreach ($mapping_status as $desc => $id) {
    $fields[$desc] = isset($map[$id]) ? $map[$id] : (-1);
    $rrd_def[] = 'DS:'.$id.':GAUGE:'.$config['rrd']['heartbeat'].':0:125000000000';
}
$status = true;
$tags = compact('name', 'app_id', 'status', 'rrd_name', 'rrd_def');
data_update($device, 'app', $tags, $fields);
