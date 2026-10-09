<?php

namespace App\Console\Commands\Traits;

use App\Models\Device;

trait CompletesDeviceArgument
{
    /**
     * Complete the device spec argument with matching device hostnames
     *
     * @return array<int, string>|false
     */
    public function completeArgument(string $name, ?string $value, mixed $previous = null): array|false
    {
        if ($name == 'device spec') {
            return Device::query()
                ->when($value, fn ($query) => $query->where('hostname', 'like', "$value%"))
                ->orderBy('hostname')
                ->limit(25)
                ->pluck('hostname')
                ->all();
        }

        return false;
    }
}
