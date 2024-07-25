<?php

DeviceCache::get($device['device_id'])->load(['links.port', 'links.remoteDevice', 'links.remotePort']);
echo view('device.tabs.ports.links', [
    'data' => [
        'links' => DeviceCache::get($device['device_id'])->links,
    ],
]);
