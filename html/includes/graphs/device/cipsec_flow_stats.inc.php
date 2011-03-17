<?php

include("includes/graphs/common.inc.php");

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/cipsec_flow.rrd";

$i=0;
$rrd_list[$i]['filename'] = $rrd_filename;
$rrd_list[$i]['descr'] = "InAuths";
$rrd_list[$i]['rra'] = "InAuths";
$i++;
$rrd_list[$i]['filename'] = $rrd_filename;
$rrd_list[$i]['descr'] = "OutAuths";
$rrd_list[$i]['rra'] = "OutAuths";
$rra_list[$i]['invert'] = "1";

$i++;
$rrd_list[$i]['filename'] = $rrd_filename;
$rrd_list[$i]['descr'] = "InDecrypts";
$rrd_list[$i]['rra'] = "InDencrypts";
$i++;
$rrd_list[$i]['filename'] = $rrd_filename;
$rrd_list[$i]['descr'] = "OutEncrypts";
$rrd_list[$i]['rra'] = "OutEncrypts";
$rra_list[$i]['invert'] = "1";

$i++;
$rrd_list[$i]['filename'] = $rrd_filename;
$rrd_list[$i]['descr'] = "InDrops";
$rrd_list[$i]['rra'] = "InDrops";
$i++;
$rrd_list[$i]['filename'] = $rrd_filename;
$rrd_list[$i]['descr'] = "InReplayDrops";
$rrd_list[$i]['rra'] = "InReplayDrops";
$i++;
$rrd_list[$i]['filename'] = $rrd_filename;
$rrd_list[$i]['descr'] = "OutDrops";
$rrd_list[$i]['rra'] = "OutDrops";
$rra_list[$i]['invert'] = "1";

$i++;
$rrd_list[$i]['filename'] = $rrd_filename;
$rrd_list[$i]['descr'] = "InAuthFail";
$rrd_list[$i]['rra'] = "InAuthFails";
$i++;
$rrd_list[$i]['filename'] = $rrd_filename;
$rrd_list[$i]['descr'] = "OutAuthFail";
$rrd_list[$i]['rra'] = "OutAuthFails";
$rra_list[$i]['invert'] = "1";

$i++;
$rrd_list[$i]['filename'] = $rrd_filename;
$rrd_list[$i]['descr'] = "InDecryptFails";
$rrd_list[$i]['rra'] = "InDecryptFails";
$i++;
$rrd_list[$i]['filename'] = $rrd_filename;
$rrd_list[$i]['descr'] = "OutEncryptFails";
$rrd_list[$i]['rra'] = "OutEncryptFails";
$rra_list[$i]['invert'] = "1";

$i++;
$rrd_list[$i]['filename'] = $rrd_filename;
$rrd_list[$i]['descr'] = "ProtocolUseFails";
$rrd_list[$i]['rra'] = "ProtocolUseFails";
$i++;
$rrd_list[$i]['filename'] = $rrd_filename;
$rrd_list[$i]['descr'] = "NoSaFails";
$rrd_list[$i]['rra'] = "NoSaFails";
$i++;
$rrd_list[$i]['filename'] = $rrd_filename;
$rrd_list[$i]['descr'] = "SysCapFails";
$rrd_list[$i]['rra'] = "SysCapFails";

#$units='%';
#$total_units='%';
$colours='mixed';

$scale_min = "0";
#$scale_max = "100";

$nototal = 1;

include("includes/graphs/generic_multi_line.inc.php");

?>