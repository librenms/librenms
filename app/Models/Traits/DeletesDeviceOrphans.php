<?php

namespace App\Models\Traits;

use App\Models\Device;
use Illuminate\Database\Eloquent\Collection;

trait DeletesDeviceOrphans
{
    /**
     * Delete rows whose device no longer exists. Selects ids first so the delete
     * never joins devices: a delete that does takes shared locks across it and
     * deadlocks against concurrent polls.
     */
    public static function deleteOrphans(): int
    {
        $key = (new static)->getKeyName();
        $deleted = 0;

        static::query()
            ->select($key)
            ->whereNotIn(static::deviceForeignKey(), Device::query()->select('device_id'))
            ->chunkById(1000, function (Collection $rows) use ($key, &$deleted): void {
                $deleted += static::query()->whereIntegerInRaw($key, $rows->pluck($key))->delete();
            });

        return $deleted;
    }

    protected static function deviceForeignKey(): string
    {
        return 'device_id';
    }
}
