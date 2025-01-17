<?php

use App\Facades\DeviceCache;

$valid_storage = [];

// Include all discovery modules
foreach (glob(Config::get('install_dir') . '/includes/discovery/storage/*.inc.php') as $file) {
    include $file;
}

Log::debug($valid_storage);

// Remove storage which weren't redetected here
foreach (DeviceCache::getPrimary()->storage as $s) {
    Log::debug($s->storage_index . ' -> ' . $s->storage_mib . "\n");

    if (! $valid_storage[$s->storage_index][$s->storage_mib]) {
        echo '-';
        $s->delete();
    }
}
echo "\n";
