<?php

use App\Facades\DeviceCache;

$valid_storage = [];

// Include all discovery modules
foreach (glob(Config::get('install_dir') . '/includes/discovery/storage/*.inc.php') as $file) {
    include $file;
}

Log::debug($valid_storage);

// Remove storage which weren't redetected here
foreach (DeviceCache::getPrimary()->storage as $test_storage) {
    Log::debug($storage_index . ' -> ' . $storage_mib . "\n");

    if (! $valid_storage[$test_storage->storage_index][$test_storage->storage_mib]) {
        echo '-';
        $test_storage->delete();
    }
}
echo "\n";
