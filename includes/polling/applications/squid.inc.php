<?php

use LibreNMS\RRD\RrdDefinition;

$name = 'squid';
$app_id = $app['app_id'];
$options      = '-O qv';
$mib          = 'NET-SNMP-EXTEND-MIB';
$oid          = 'nsExtendOutLine.5.115.113.117.105.100';
$returned = snmp_walk($device, $oid, $options, $mib);

# cacheCurrentLRUExpiration is returned here, but we don't use it as it would be more work to exclude it from the extend
list( $MemMaxSize, $SwapMaxSize, $SwapHighWM, $SwapLowWM, $SysPageFaults, $SysNumReads, $MemUsage, $CpuTime, $CpuUsage,
    $MaxResSize, $NumObjCount, $CurrentLRUExpiration, $CurrentUnlinkRequests, $CurrentUnusedFDescrCnt,
    $CurrentResFileDescrCnt, $CurrentFileDescrCnt, $CurrentFileDescrMax, $ProtoClientHttpRequests, $HttpHits,
    $HttpErrors, $HttpInKb, $HttpOutKb, $IcpPktsSent, $IcpPktsRecv, $IcpKbSent, $IcpKbRecv, $ServerRequests,
    $ServerErrors, $ServerInKb, $ServerOutKb, $CurrentSwapSize, $Clients, $RequestHitRatio1, $RequestHitRatio5,
    $RequestHitRatio60, $RequestByteRatio1, $RequestByteRatio5, $RequestByteRatio60,  ) = explode("\n", $returned);

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
