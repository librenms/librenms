<?php

require 'includes/html/graphs/common.inc.php';

$rrd_filename = Rrd::name($device['hostname'], 'cipsec_flow');

$i = 0;
$rrd_list[$i]['filename'] = $rrd_filename;
$rrd_list[$i]['descr'] = 'InAuths';
$rrd_list[$i]['ds'] = 'InAuths';
$i++;
$rrd_list[$i]['filename'] = $rrd_filename;
$rrd_list[$i]['descr'] = 'OutAuths';
$rrd_list[$i]['ds'] = 'OutAuths';
$ds_list[$i]['invert'] = '1';

$i++;
$rrd_list[$i]['filename'] = $rrd_filename;
$rrd_list[$i]['descr'] = 'InDecrypts';
$rrd_list[$i]['ds'] = 'InDencrypts';
$i++;
$rrd_list[$i]['filename'] = $rrd_filename;
$rrd_list[$i]['descr'] = 'OutEncrypts';
$rrd_list[$i]['ds'] = 'OutEncrypts';
$ds_list[$i]['invert'] = '1';

$i++;
$rrd_list[$i]['filename'] = $rrd_filename;
$rrd_list[$i]['descr'] = 'InDrops';
$rrd_list[$i]['ds'] = 'InDrops';
$i++;
$rrd_list[$i]['filename'] = $rrd_filename;
$rrd_list[$i]['descr'] = 'InReplayDrops';
$rrd_list[$i]['ds'] = 'InReplayDrops';
$i++;
$rrd_list[$i]['filename'] = $rrd_filename;
$rrd_list[$i]['descr'] = 'OutDrops';
$rrd_list[$i]['ds'] = 'OutDrops';
$ds_list[$i]['invert'] = '1';

$i++;
$rrd_list[$i]['filename'] = $rrd_filename;
$rrd_list[$i]['descr'] = 'InAuthFail';
$rrd_list[$i]['ds'] = 'InAuthFails';
$i++;
$rrd_list[$i]['filename'] = $rrd_filename;
$rrd_list[$i]['descr'] = 'OutAuthFail';
$rrd_list[$i]['ds'] = 'OutAuthFails';
$ds_list[$i]['invert'] = '1';

$i++;
$rrd_list[$i]['filename'] = $rrd_filename;
$rrd_list[$i]['descr'] = 'InDecryptFails';
$rrd_list[$i]['ds'] = 'InDecryptFails';
$i++;
$rrd_list[$i]['filename'] = $rrd_filename;
$rrd_list[$i]['descr'] = 'OutEncryptFails';
$rrd_list[$i]['ds'] = 'OutEncryptFails';
$ds_list[$i]['invert'] = '1';

$i++;
$rrd_list[$i]['filename'] = $rrd_filename;
$rrd_list[$i]['descr'] = 'ProtocolUseFails';
$rrd_list[$i]['ds'] = 'ProtocolUseFails';
$i++;
$rrd_list[$i]['filename'] = $rrd_filename;
$rrd_list[$i]['descr'] = 'NoSaFails';
$rrd_list[$i]['ds'] = 'NoSaFails';
$i++;
$rrd_list[$i]['filename'] = $rrd_filename;
$rrd_list[$i]['descr'] = 'SysCapFails';
$rrd_list[$i]['ds'] = 'SysCapFails';

// $units='%';
// $total_units='%';
$colours = 'mixed';

$scale_min = '0';
// $scale_max = "100";
$nototal = 1;

require 'includes/html/graphs/generic_multi_line.inc.php';
