<?php

namespace App\Data\TimeSeries\Rrd;

use App\Data\TimeSeries\Contracts\RrdPathResolver;
use App\Data\TimeSeries\MetricIdentity;
use App\Facades\DeviceCache;
use InvalidArgumentException;
use LibreNMS\RRD\RrdPath;

class LegacyRrdPathResolver implements RrdPathResolver
{
    public function resolve(MetricIdentity $identity): RrdPath
    {
        $labels = $identity->labels;
        $deviceId = $labels['device_id'] ?? null;
        if ($deviceId === null) {
            throw new InvalidArgumentException('Metric identity must have a device_id label.');
        }
        unset($labels['device_id']);

        $hostname = DeviceCache::get($deviceId)->hostname;
        if (empty($hostname)) {
            throw new InvalidArgumentException("Could not resolve hostname for device_id: $deviceId");
        }

        $extra = array_merge([$identity->name], array_values($labels));

        return RrdPath::make((string) $hostname, implode('-', $extra) . '.rrd');
    }
}
