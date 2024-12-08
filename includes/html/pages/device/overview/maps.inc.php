<?php

$maps = DeviceCache::getPrimary()->maps;

if ($maps->isNotEmpty()) {
    echo view('device.overview.maps', ['maps' => $maps->sortBy('name')]);
}
