<?php

namespace LibreNMS\Data\Store;

use App\Facades\LibrenmsConfig;
use App\Polling\Measure\Measurement;
use Illuminate\Support\Facades\Log;

class Redis extends BaseDatastore
{
    private readonly bool $enabled;
    private readonly string $connection;
    private readonly string $defaultListKey;
    private readonly string $servicesListKey;
    private readonly string $discoveryListKey;
    private readonly int $maxLength;
    private readonly int $defaultTtl;
    private readonly int $servicesTtl;
    private readonly int $discoveryTtl;

    public function __construct()
    {
        parent::__construct();

        $this->enabled = self::isEnabled();
        $this->connection = (string) config('database.redis.metrics.connection', 'metrics');
        $this->defaultListKey = (string) config('database.redis.metrics.poller_key', 'librenms:metrics:poller');
        $this->servicesListKey = (string) config('database.redis.metrics.services_key', $this->defaultListKey . ':services');
        $this->discoveryListKey = (string) config('database.redis.metrics.discovery_key', $this->defaultListKey . ':discovery');
        $this->maxLength = max((int) config('database.redis.metrics.poller_max_length', 50000), 0);
        $this->defaultTtl = $this->computeTtlSeconds('service_poller_frequency', 300);
        $this->servicesTtl = $this->computeTtlSeconds('service_services_frequency', 300);
        $this->discoveryTtl = $this->computeTtlSeconds('service_discovery_frequency', 21600);
    }

    public function getName(): string
    {
        return 'Redis';
    }

    public static function isEnabled(): bool
    {
        return LibrenmsConfig::get('redis.enable', false);
    }

    /**
     * @inheritDoc
     */
    public function write(string $measurement, array $fields, array $tags = [], array $meta = []): void
    {
        if (! $this->enabled) {
            return;
        }

        $stat = Measurement::start('write');

        try {
            $device = $this->getDevice($meta);

            $payload = [
                'timestamp' => time(),
                'measurement' => $measurement,
                'device_id' => $device->device_id,
                'hostname' => $device->hostname,
                'status' => isset($device->status) ? ((int) $device->status > 0 ? 1 : 0) : null,
                'status_reason' => isset($device->status_reason) ? (string) $device->status_reason : null,
                'fields' => $fields,
                'tags' => array_filter($tags, fn ($value) => $value !== '' && $value !== null),
            ];

            $encoded = json_encode($payload, JSON_UNESCAPED_SLASHES);
            if ($encoded === false) {
                Log::warning('Redis datastore payload encode failed.', [
                    'measurement' => $measurement,
                    'device_id' => $device->device_id,
                ]);

                return;
            }

            $listKey = $this->resolveListKeyForMeasurement($measurement);
            $ttl = $this->resolveTtlForMeasurement($measurement);

            $redis = app('redis')->connection($this->connection);
            $redis->rPush($listKey, $encoded);

            if ($this->maxLength > 0) {
                $redis->ltrim($listKey, -$this->maxLength, -1);
            }

            if ($ttl > 0) {
                $redis->expire($listKey, $ttl);
            }

            $this->recordStatistic($stat->end());
        } catch (\Throwable $e) {
            Log::error('Redis datastore write failed.', [
                'measurement' => $measurement,
                'connection' => $this->connection,
                'key' => $this->resolveListKeyForMeasurement($measurement),
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function computeTtlSeconds(string $configKey, int $defaultFrequency): int
    {
        $frequency = max((int) LibrenmsConfig::get($configKey, $defaultFrequency), 1);

        return max((int) ceil($frequency * 1.5), 1);
    }

    private function resolveListKeyForMeasurement(string $measurement): string
    {
        if ($measurement === 'services') {
            return $this->servicesListKey;
        }

        if (in_array($measurement, ['discover', 'discover-perf', 'discovery', 'discovery-perf', 'last-discovered-perf'], true)) {
            return $this->discoveryListKey;
        }

        return $this->defaultListKey;
    }

    private function resolveTtlForMeasurement(string $measurement): int
    {
        if ($measurement === 'services') {
            return $this->servicesTtl;
        }

        if (in_array($measurement, ['discover', 'discover-perf', 'discovery', 'discovery-perf', 'last-discovered-perf'], true)) {
            return $this->discoveryTtl;
        }

        return $this->defaultTtl;
    }
}
