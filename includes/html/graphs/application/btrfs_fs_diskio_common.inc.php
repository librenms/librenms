<?php

require 'includes/html/graphs/common.inc.php';
require 'includes/html/graphs/application/app_diskio_common.inc.php';

$fs_param = $vars['fs'] ?? null;
if (! is_string($fs_param) || $fs_param === '') {
    throw new LibreNMS\Exceptions\RrdGraphException('No filesystem selected');
}

$discovery_fs = $app->data['discovery']['filesystems'][$fs_param] ?? null;
if (! is_array($discovery_fs)) {
    throw new LibreNMS\Exceptions\RrdGraphException('Unknown filesystem: ' . $fs_param);
}

$fs_uuid = $fs_param;
$fs = $discovery_fs['mountpoint'] ?? $fs_uuid;
$device_map = $discovery_fs['devices'] ?? [];
$device_metadata = $discovery_fs['device_metadata'] ?? [];

if (! is_array($device_map) || count($device_map) === 0) {
    throw new LibreNMS\Exceptions\RrdGraphException('No filesystem devices');
}

$candidateSets = [];
foreach ($device_map as $dev_id => $dev_path) {
    $candidates = [];
    $meta = is_array($device_metadata[$dev_id] ?? null) ? $device_metadata[$dev_id] : [];
    $backing = trim((string) ($meta['backing_path'] ?? ''));
    if ($backing !== '') {
        $candidates[] = $backing;
        $candidates[] = ltrim((string) preg_replace('#^/dev/#', '', $backing), '/');
        $candidates[] = basename($backing);
    }
    $candidates[] = (string) $dev_path;
    $candidates[] = ltrim((string) preg_replace('#^/dev/#', '', $dev_path), '/');
    $candidates[] = basename((string) $dev_path);
    $candidateSets[] = array_values(array_unique($candidates));
}

$rrd_list = app_diskio_build_rrd_list($device, $candidateSets, 'filesystem');
