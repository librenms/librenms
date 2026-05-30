<?php

namespace LibreNMS\Data\Store;

use App\Facades\LibrenmsConfig;
use App\Polling\Measure\Measurement;
use Illuminate\Support\Facades\Log;

class Redis extends BaseDatastore
{
    private bool $enabled;
    private string $connection;
    private string $listKey;
    private int $maxLength;

    public function __construct()
    {
        parent::__construct();

        $this->enabled = self::isEnabled();
        $this->connection = (string) config('database.redis.metrics.connection', 'metrics');
        $this->listKey = (string) config('database.redis.metrics.poller_key', 'librenms:metrics:poller');
        $this->maxLength = max((int) config('database.redis.metrics.poller_max_length', 50000), 0);
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

            $redis = app('redis')->connection($this->connection);
            $redis->rpush($this->listKey, $encoded);

            if ($this->maxLength > 0) {
                $redis->ltrim($this->listKey, -$this->maxLength, -1);
            }

            $this->recordStatistic($stat->end());
        } catch (\Throwable $e) {
            Log::error('Redis datastore write failed.', [
                'measurement' => $measurement,
                'connection' => $this->connection,
                'key' => $this->listKey,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
