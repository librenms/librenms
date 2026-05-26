<?php

require 'includes/html/graphs/common.inc.php';
require 'includes/html/graphs/application/app_diskio_common.inc.php';

$arrayParam = $vars['array'] ?? null;
if (! is_string($arrayParam) || $arrayParam === '') {
    throw new LibreNMS\Exceptions\RrdGraphException('No array selected');
}

$dbArray = App\Models\MdadmArray::where('app_id', $app->app_id)
    ->where(function ($q) use ($arrayParam): void {
        $q->where('uuid', $arrayParam)->orWhere('array_name', $arrayParam)->orWhere('md_id', $arrayParam);
    })
    ->with('drives')
    ->first();

if ($dbArray === null) {
    throw new LibreNMS\Exceptions\RrdGraphException('Unknown array: ' . $arrayParam);
}

$drives = $dbArray->drives;
if ($drives->isEmpty()) {
    throw new LibreNMS\Exceptions\RrdGraphException('No array devices');
}

$candidateSets = [];
foreach ($drives as $drive) {
    $path = trim((string) ($drive->path ?? $drive->dev_id ?? ''));
    if ($path === '') {
        continue;
    }
    $candidateSets[] = array_values(array_unique([
        $path,
        ltrim((string) preg_replace('#^/dev/#', '', $path), '/'),
        basename($path),
    ]));
}

$rrd_list = app_diskio_build_rrd_list($device, $candidateSets, 'array');
