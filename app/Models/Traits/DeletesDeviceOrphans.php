<?php

namespace App\Models\Traits;

use App\Models\Device;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait DeletesDeviceOrphans
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Device, $this>
     */
    abstract public function device(): BelongsTo;

    /**
     * Delete rows whose device no longer exists. Selects ids first so the delete
     * never joins devices: a delete that does takes shared locks across it and
     * deadlocks against concurrent polls.
     */
    public static function deleteOrphans(): int
    {
        $model = new static;
        $key = $model->getKeyName();
        $deleted = 0;

        static::query()
            ->select($key)
            ->whereNotIn($model->device()->getForeignKeyName(), Device::query()->select('device_id'))
            ->chunkById(1000, function (Collection $rows) use ($key, &$deleted): void {
                $deleted += static::query()->whereIntegerInRaw($key, $rows->pluck($key))->delete();
            });

        return $deleted;
    }
}
