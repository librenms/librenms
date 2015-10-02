<?php

// FIXME - this is lame
if (!empty($agent_data['app']['mysql'])) {
    $mysql = $agent_data['app']['mysql'];
}
else {
    // Polls MySQL  statistics from script via SNMP
    $mysql_cmd  = $config['snmpget'].' -m NET-SNMP-EXTEND-MIB -O qv '.snmp_gen_auth($device).' '.$device['hostname'].':'.$device['port'];
    $mysql_cmd .= ' nsExtendOutputFull.5.109.121.115.113.108';
    $mysql      = shell_exec($mysql_cmd);
}

$mysql_rrd = $config['rrd_dir'].'/'.$device['hostname'].'/app-mysql-'.$app['app_id'].'.rrd';

echo ' mysql';

$data = explode("\n", $mysql);

$map = array();
foreach ($data as $str) {
    list($key, $value) = explode(':', $str);
    $map[$key]         = (float) trim($value);
    // $nstring .= (float)trim($elements[1]).":";
}

// General Stats
$mapping = array(
    'IDBLBSe' => 'cr',
    'IBLFh'   => 'ct',
    'IBLWn'   => 'cu',
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

$values = array();
foreach ($mapping as $k => $v) {
    $fields[$k] = isset($map[$v]) ? $map[$v] : (-1);
}

$string = implode(':', $values);

if (!is_file($mysql_rrd)) {
    rrdtool_create(
        $mysql_rrd,
        '--step 300 
        DS:IDBLBSe:GAUGE:600:0:125000000000 
        DS:IBLFh:DERIVE:600:0:125000000000 
        DS:IBLWn:DERIVE:600:0:125000000000 
        DS:SRows:DERIVE:600:0:125000000000 
        DS:SRange:DERIVE:600:0:125000000000 
        DS:SMPs:DERIVE:600:0:125000000000 
        DS:SScan:DERIVE:600:0:125000000000 
        DS:IBIRd:DERIVE:600:0:125000000000 
        DS:IBIWr:DERIVE:600:0:125000000000 
        DS:IBILg:DERIVE:600:0:125000000000 
        DS:IBIFSc:DERIVE:600:0:125000000000 
        DS:IDBRDd:DERIVE:600:0:125000000000 
        DS:IDBRId:DERIVE:600:0:125000000000 
        DS:IDBRRd:DERIVE:600:0:125000000000 
        DS:IDBRUd:DERIVE:600:0:125000000000 
        DS:IBRd:DERIVE:600:0:125000000000 
        DS:IBCd:DERIVE:600:0:125000000000 
        DS:IBWr:DERIVE:600:0:125000000000 
        DS:TLIe:DERIVE:600:0:125000000000 
        DS:TLWd:DERIVE:600:0:125000000000 
        DS:IBPse:GAUGE:600:0:125000000000 
        DS:IBPDBp:GAUGE:600:0:125000000000 
        DS:IBPFe:GAUGE:600:0:125000000000 
        DS:IBPMps:GAUGE:600:0:125000000000 
        DS:TOC:GAUGE:600:0:125000000000 
        DS:OFs:GAUGE:600:0:125000000000 
        DS:OTs:GAUGE:600:0:125000000000 
        DS:OdTs:COUNTER:600:0:125000000000 
        DS:IBSRs:DERIVE:600:0:125000000000 
        DS:IBSWs:DERIVE:600:0:125000000000 
        DS:IBOWs:DERIVE:600:0:125000000000 
        DS:QCs:GAUGE:600:0:125000000000 
        DS:QCeFy:GAUGE:600:0:125000000000 
        DS:MaCs:GAUGE:600:0:125000000000 
        DS:MUCs:GAUGE:600:0:125000000000 
        DS:ACs:DERIVE:600:0:125000000000 
        DS:AdCs:DERIVE:600:0:125000000000 
        DS:TCd:GAUGE:600:0:125000000000 
        DS:Cs:DERIVE:600:0:125000000000 
        DS:IBTNx:DERIVE:600:0:125000000000 
        DS:KRRs:DERIVE:600:0:125000000000 
        DS:KRs:DERIVE:600:0:125000000000 
        DS:KWR:DERIVE:600:0:125000000000 
        DS:KWs:DERIVE:600:0:125000000000 
        DS:QCQICe:DERIVE:600:0:125000000000 
        DS:QCHs:DERIVE:600:0:125000000000 
        DS:QCIs:DERIVE:600:0:125000000000 
        DS:QCNCd:DERIVE:600:0:125000000000 
        DS:QCLMPs:DERIVE:600:0:125000000000 
        DS:CTMPDTs:DERIVE:600:0:125000000000 
        DS:CTMPTs:DERIVE:600:0:125000000000 
        DS:CTMPFs:DERIVE:600:0:125000000000 
        DS:IBIIs:DERIVE:600:0:125000000000 
        DS:IBIMRd:DERIVE:600:0:125000000000 
        DS:IBIMs:DERIVE:600:0:125000000000 
        DS:IBILog:DERIVE:602:0:125000000000 
        DS:IBISc:DERIVE:602:0:125000000000 
        DS:IBIFLg:DERIVE:600:0:125000000000 
        DS:IBFBl:DERIVE:600:0:125000000000 
        DS:IBIIAo:DERIVE:600:0:125000000000 
        DS:IBIAd:DERIVE:600:0:125000000000 
        DS:IBIAe:DERIVE:600:0:125000000000 
        DS:SFJn:DERIVE:600:0:125000000000 
        DS:SFRJn:DERIVE:600:0:125000000000 
        DS:SRe:DERIVE:600:0:125000000000 
        DS:SRCk:DERIVE:600:0:125000000000 
        DS:SSn:DERIVE:600:0:125000000000 
        DS:SQs:DERIVE:600:0:125000000000 
        DS:BRd:DERIVE:600:0:125000000000 
        DS:BSt:DERIVE:600:0:125000000000 
        DS:CDe:DERIVE:600:0:125000000000 
        DS:CIt:DERIVE:600:0:125000000000 
        DS:CISt:DERIVE:600:0:125000000000 
        DS:CLd:DERIVE:600:0:125000000000 
        DS:CRe:DERIVE:600:0:125000000000 
        DS:CRSt:DERIVE:600:0:125000000000 
        DS:CSt:DERIVE:600:0:125000000000 
        DS:CUe:DERIVE:600:0:125000000000 
        DS:CUMi:DERIVE:600:0:125000000000 '.$config['rrd_rra']
    );
}//end if

rrdtool_update($mysql_rrd, $fields);

// Process state statistics
$mysql_status_rrd = $config['rrd_dir'].'/'.$device['hostname'].'/app-mysql-'.$app['app_id'].'-status.rrd';

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

$values     = array();
$rrd_create = '';
unset($fields);
foreach ($mapping_status as $desc => $id) {
    $fields[$desc]    = isset($map[$id]) ? $map[$id] : (-1);
    $rrd_create .= ' DS:'.$id.':GAUGE:600:0:125000000000';
}

$string = implode(':', $values);

if (!is_file($mysql_status_rrd)) {
    rrdtool_create($mysql_status_rrd, '--step 300 '.$rrd_create.' '.$config['rrd_rra']);
}

rrdtool_update($mysql_status_rrd, $fields);
