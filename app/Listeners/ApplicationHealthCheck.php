<?php

namespace App\Listeners;

use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApplicationHealthCheck
{
    public function handle(DiagnosingHealth $event): void
    {
        // Database
        DB::connection()->getPdo();

        // Cache
        $key = 'health:ping:' . Str::random();
        Cache::put($key, '1', 5);
        if (Cache::pull($key) !== '1') {
            throw new \RuntimeException('Cache health check value could not be read back.');
        }
    }
}
