<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'squid';
$app_id = $app['app_id'];

+$OIDs=array(
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
    '.1.3.6.1.4.1.3495.1.3.2.2.1.10.60'
);
$returnedOIDs=snmp_get_multi_oid($device, $OIDs); 

$MemMaxSize = $returnedOIDs['.1.3.6.1.4.1.3495.1.2.5.1.0'];
$SwapMaxSize = $returnedOIDs['.1.3.6.1.4.1.3495.1.2.5.2.0'];
$SwapHighWM = $returnedOIDs['.1.3.6.1.4.1.3495.1.2.5.3.0'];
$SwapLowWM = $returnedOIDs['.1.3.6.1.4.1.3495.1.2.5.4.0'];
$SysPageFaults = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.1.1.0'];
$SysNumReads = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.1.2.0'];
$MemUsage = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.1.3.0'];
$CpuTime = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.1.4.0'];
$CpuUsage = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.1.5.0'];
$MaxResSize = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.1.6.0'];
$NumObjCount = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.1.7.0'];
$CurrentLRUExpiration = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.1.8.0'];
$CurrentUnlinkRequests = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.1.9.0'];
$CurrentUnusedFDescrCnt = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.1.10.0'];
$CurrentResFileDescrCnt = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.1.11.0'];
$CurrentFileDescrCnt = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.1.12.0'];
$CurrentFileDescrMax = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.1.13.0'];
$ProtoClientHttpRequests = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.2.1.1.0'];
$HttpHits = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.2.1.2.0'];
$HttpErrors = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.2.1.3.0'];
$HttpInKb = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.2.1.4.0'];
$HttpOutKb = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.2.1.5.0'];
$IcpPktsSent = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.2.1.6.0'];
$IcpPktsRecv = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.2.1.7.0'];
$IcpKbSent = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.2.1.8.0'];
$IcpKbRecv = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.2.1.9.0'];
$ServerRequests = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.2.1.10.0'];
$ServerErrors = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.2.1.11.0'];
$ServerInKb = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.2.1.12.0'];
$ServerOutKb = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.2.1.13.0'];
$CurrentSwapSize = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.2.1.14.0'];
$Clients = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.2.1.15.0'];
$RequestHitRatio1 = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.2.2.1.9.1'];
$RequestHitRatio5 = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.2.2.1.9.5'];
$RequestHitRatio60 = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.2.2.1.9.60'];
$RequestByteRatio1 = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.2.2.1.10.1'];
$RequestByteRatio5 = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.2.2.1.10.5'];
$RequestByteRatio60 = $returnedOIDs['.1.3.6.1.4.1.3495.1.3.2.2.1.10.60'];

$rrd_name = array('app', $name, $app_id);

$rrd_def = RrdDefinition::make()
    ->addDataset('MemMaxSize', 'GAUGE', 0)
    ->addDataset('SwapMaxSize', 'GAUGE', 0)
    ->addDataset('SwapHighWM', 'GAUGE', 0)
    ->addDataset('SwapLowWM', 'GAUGE', 0)
    ->addDataset('SysPageFaults', 'COUNTER', 0)
    ->addDataset('SysNumReads', 'COUNTER', 0)
    ->addDataset('MemUsage', 'GAUGE', 0)
    ->addDataset('CpuTime', 'GAUGE', 0)
    ->addDataset('CpuUsage', 'GAUGE', 0)
    ->addDataset('MaxResSize', 'GAUGE', 0)
    ->addDataset('NumObjCount', 'GAUGE', 0)
    ->addDataset('CurUnlinkReq', 'GAUGE', 0)
    ->addDataset('CurUnusedFDescrCnt', 'GAUGE', 0)
    ->addDataset('CurResFileDescrCnt', 'GAUGE', 0)
    ->addDataset('CurFileDescrCnt', 'GAUGE', 0)
    ->addDataset('CurFileDescrMax', 'GAUGE', 0)
    ->addDataset('ProtoClientHttpReq', 'COUNTER', 0)
    ->addDataset('HttpHits', 'COUNTER', 0)
    ->addDataset('HttpErrors', 'COUNTER', 0)
    ->addDataset('HttpInKb', 'COUNTER', 0)
    ->addDataset('HttpOutKb', 'COUNTER', 0)
    ->addDataset('IcpPktsSent', 'COUNTER', 0)
    ->addDataset('IcpPktsRecv', 'COUNTER', 0)
    ->addDataset('IcpKbSent', 'COUNTER', 0)
    ->addDataset('IcpKbRecv', 'COUNTER', 0)
    ->addDataset('ServerRequests', 'COUNTER', 0)
    ->addDataset('ServerErrors', 'COUNTER', 0)
    ->addDataset('ServerInKb', 'COUNTER', 0)
    ->addDataset('ServerOutKb', 'COUNTER', 0)
    ->addDataset('CurrentSwapSize', 'GAUGE', 0)
    ->addDataset('Clients', 'GAUGE', 0)
    ->addDataset('ReqHitRatio1', 'GAUGE', 0)
    ->addDataset('ReqHitRatio5', 'GAUGE', 0)
    ->addDataset('ReqHitRatio60', 'GAUGE', 0)
    ->addDataset('ReqByteRatio1', 'GAUGE', 0)
    ->addDataset('ReqByteRatio5', 'GAUGE', 0)
    ->addDataset('ReqByteRatio60', 'GAUGE', 0);

$MemMaxSize=$MemMaxSize*1000;
$SwapMaxSize=$SwapMaxSize*1000;
$SwapHighWM=$SwapHighWM*1000;
$SwapLowWM=$SwapLowWM*1000;

$fields = array(
    "MemMaxSize" => $MemMaxSize,
    "SwapMaxSize" => $SwapMaxSize,
    "SwapHighWM" => $SwapHighWM,
    "SwapLowWM" => $SwapLowWM,
    "SysPageFaults" => $SysPageFaults,
    "SysNumReads" => $SysNumReads,
    "MemUsage" => $MemUsage,
    "CpuTime" => $CpuTime,
    "CpuUsage" => $CpuUsage,
    "MaxResSize" => $MaxResSize,
    "NumObjCount" => $NumObjCount,
    "CurUnlinkReq" => $CurrentUnlinkRequests,
    "CurUnusedFDescrCnt" => $CurrentUnusedFDescrCnt,
    "CurResFileDescrCnt" => $CurrentResFileDescrCnt,
    "CurFileDescrCnt" => $CurrentFileDescrCnt,
    "CurFileDescrMax" => $CurrentFileDescrMax,
    "ProtoClientHttpReq" => $ProtoClientHttpRequests,
    "HttpHits" => $HttpHits,
    "HttpErrors" => $HttpErrors,
    "HttpInKb" => $HttpInKb,
    "HttpOutKb" => $HttpOutKb,
    "IcpPktsSent" => $IcpPktsSent,
    "IcpPktsRecv" => $IcpPktsRecv,
    "IcpKbSent" => $IcpKbSent,
    "IcpKbRecv" => $IcpKbRecv,
    "ServerRequests" => $ServerRequests,
    "ServerErrors" => $ServerErrors,
    "ServerInKb" => $ServerInKb,
    "ServerOutKb" => $ServerOutKb,
    "CurrentSwapSize" => $CurrentSwapSize,
    "Clients" => $Clients,
    "ReqHitRatio1" => $RequestHitRatio1,
    "ReqHitRatio5" => $RequestHitRatio5,
    "ReqHitRatio60" => $RequestHitRatio60,
    "ReqByteRatio1" => $RequestByteRatio1,
    "ReqByteRatio5" => $RequestByteRatio5,
    "ReqByteRatio60" => $RequestByteRatio60,
);

$tags = array('name' => $name, 'app_id' => $app_id, 'rrd_def' => $rrd_def, 'rrd_name' => $rrd_name);
data_update($device, 'app', $tags, $fields);
