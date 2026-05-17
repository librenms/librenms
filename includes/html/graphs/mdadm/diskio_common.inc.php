<?php

require 'includes/html/graphs/common.inc.php';
require 'includes/html/graphs/application/app_diskio_common.inc.php';

$arrayParam = $vars['array'] ?? null;
if (! is_string($arrayParam) || $arrayParam === '') {
    throw new LibreNMS\Exceptions\RrdGraphException('No array selected');
}

$arrays = (array) ($app->data['arrays'] ?? []);
$selected = null;
foreach ($arrays as $uuid => $entry) {
    if (! is_array($entry) || ! is_array($entry['array'] ?? null)) {
        continue;
    }

    $name = (string) ($entry['array']['name'] ?? $uuid);
    if ($arrayParam === $uuid || $arrayParam === $name) {
        $selected = $entry;
        break;
    }
}

if (! is_array($selected)) {
    throw new LibreNMS\Exceptions\RrdGraphException('Unknown array: ' . $arrayParam);
}

$devices = is_array($selected['devices'] ?? null) ? $selected['devices'] : [];
if (count($devices) === 0) {
    throw new LibreNMS\Exceptions\RrdGraphException('No array devices');
}

$candidateSets = [];
foreach ($devices as $devId => $dev) {
    $path = trim((string) (is_array($dev) ? ($dev['path'] ?? $dev['device_name'] ?? $devId) : $devId));
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
