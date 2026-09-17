<?php

namespace App\Observers;

use App\Facades\LibrenmsConfig;
use App\Models\Storage;

class StorageObserver
{
    public function creating(Storage $storage): void
    {
        $storage->storage_perc_warn ??= LibrenmsConfig::get('storage_perc_warn');
    }
}
