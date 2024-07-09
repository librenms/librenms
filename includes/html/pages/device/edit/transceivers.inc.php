<?php

DeviceCache::get($device['device_id'])->load(['transceivers.port', 'transceivers.metrics']);

echo view('device.edit.transceivers', ['device' => DeviceCache::get($device['device_id'])]);
