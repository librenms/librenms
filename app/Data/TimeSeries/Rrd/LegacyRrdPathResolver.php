<?php

namespace App\Data\TimeSeries\Rrd;

use App\Data\TimeSeries\Contracts\RrdPathResolver;
use App\Data\TimeSeries\MetricIdentity;
use App\Facades\DeviceCache;
use App\Facades\Rrd;
use InvalidArgumentException;

class LegacyRrdPathResolver implements RrdPathResolver
{
    public function resolve(MetricIdentity $identity): string
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

        $safeExtra = Rrd::safeName(implode('-', $extra));
        $safeHost = Rrd::safeName(trim((string) $hostname, '[]'));

        return $safeHost . '/' . $safeExtra . '.rrd';
    }
}
