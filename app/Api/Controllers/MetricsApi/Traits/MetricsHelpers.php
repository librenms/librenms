<?php

namespace App\Api\Controllers\MetricsApi\Traits;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait MetricsHelpers
{
    /**
     * Parse a metrics scope query parameter with normalization and allow-listing.
     *
     * @param  array<int, string>  $allowed
     */
    private function parseScope(Request $request, string $default = 'global', array $allowed = ['global', 'detail']): string
    {
        $scope = strtolower(trim((string) $request->input('scope', $default)));

        if ($scope === '' || ! in_array($scope, $allowed, true)) {
            return $default;
        }

        return $scope;
    }

    /**
     * Parse device / device group filters from the Request.
     * Supports:
     * - device_id (single), device_ids (comma-separated)
     * - hostname (single), hostnames (comma-separated)
     * - device_group (id or name) which will expand to device ids in that group
     * Returns an array with key 'device_ids' => Collection|null
     *
     * @return array{device_ids: Collection<int, int|string>|null}
     */
    private function parseDeviceFilters(Request $request): array
    {
        $deviceIds = collect();

        // single or comma-separated device ids
        if ($request->filled('device_id')) {
            $deviceIds = $deviceIds->merge(array_map(trim(...), explode(',', $request->input('device_id'))));
        }
        if ($request->filled('device_ids')) {
            $deviceIds = $deviceIds->merge(array_map(trim(...), explode(',', $request->input('device_ids'))));
        }

        // hostname(s)
        $hostnames = collect();
        if ($request->filled('hostname')) {
            $hostnames = $hostnames->merge(array_map(trim(...), explode(',', $request->input('hostname'))));
        }
        if ($request->filled('hostnames')) {
            $hostnames = $hostnames->merge(array_map(trim(...), explode(',', $request->input('hostnames'))));
        }
        if ($hostnames->isNotEmpty()) {
            $fromHost = Device::whereIn('hostname', $hostnames)->orWhereIn('sysName', $hostnames)->pluck('device_id');
            $deviceIds = $deviceIds->merge($fromHost);
        }

        // device_group may be id (numeric) or name; expand to device ids
        if ($request->filled('device_group')) {
            $dg = trim($request->input('device_group'));
            if (ctype_digit($dg)) {
                $groupId = (int) $dg;
                $fromGroup = DB::table('device_group_device')->where('device_group_id', $groupId)->pluck('device_id');
            } else {
                $fromGroup = DB::table('device_groups')->where('name', $dg)->join('device_group_device', 'device_groups.id', '=', 'device_group_device.device_group_id')->pluck('device_group_device.device_id');
            }
            $deviceIds = $deviceIds->merge($fromGroup);
        }

        $deviceIds = $deviceIds->filter(fn ($v) => $v !== null && $v !== '')->unique()->values();

        return ['device_ids' => $deviceIds->isEmpty() ? null : $deviceIds];
    }

    /**
     * Apply a device filter (when provided) to an Eloquent/QueryBuilder instance.
     *
     * @param  mixed  $query
     * @param  Collection<int, int|string>|null  $deviceIds
     * @return mixed
     */
    private function applyDeviceFilter(mixed $query, ?Collection $deviceIds): mixed
    {
        if ($deviceIds === null) {
            return $query;
        }

        return $query->whereIn((string) ($query->getModel() ? $query->getModel()->getTable() . '.device_id' : 'device_id'), $deviceIds->all());
    }

    /**
     * Given a collection of device ids, return a keyed map of Device models by device_id.
     * If null is given, returns an empty collection.
     *
     * @param  Collection<int, mixed>|null  $deviceIds
     * @return Collection<int, Device>
     */
    private function gatherDevicesForIds(?Collection $deviceIds): Collection
    {
        if ($deviceIds === null || $deviceIds->isEmpty()) {
            return collect();
        }

        return Device::select('device_id', 'hostname', 'sysName', 'type')
            ->whereIn('device_id', $deviceIds)
            ->get()
            ->keyBy('device_id');
    }

    /**
     * Escape a value for use in Prometheus labels
     */
    private function escapeLabel(string $v): string
    {
        return str_replace(['\\', '"', "\n"], ['\\\\', '\\"', '\\n'], $v);
    }

    /**
     * Format a set of label key=>value pairs into a Prometheus label string.
     * Values should already be escaped using escapeLabel from MetricsHelpers.
     *
     * @param  array<string, int|float|string>  $labels
     */
    private function formatLabels(array $labels): string
    {
        $parts = [];
        foreach ($labels as $k => $v) {
            $parts[] = sprintf('%s="%s"', $k, $v);
        }

        return implode(',', $parts);
    }

    /**
     * Append a metric block (HELP, TYPE, and lines) into the main lines array.
     * $metricLines may be a single formatted metric line string or an array of lines.
     *
     * @param  array<int, string>  $lines
     * @param  array<int, string>|string  $metricLines
     */
    private function appendMetricBlock(array &$lines, string $metricName, string $help, string $type, array|string $metricLines): void
    {
        $lines[] = '# HELP ' . $metricName . ' ' . $help;
        $lines[] = '# TYPE ' . $metricName . ' ' . $type;

        // Normalize to array
        $metricLinesArr = is_array($metricLines) ? $metricLines : [$metricLines];

        if (! empty($metricLinesArr)) {
            $lines = array_merge($lines, $metricLinesArr);
        }
    }

    /**
     * Read and decode poller metric payloads from Redis.
     * Optionally filters to the provided device ids.
     *
     * @param  Collection<int, int|string>|null  $deviceIds
     * @return array<int, array<string, mixed>>
     */
    private function readRedisPollerPayloads(?Collection $deviceIds = null): array
    {
        $connection = (string) config('database.redis.metrics.connection', 'metrics');
        $defaultKey = (string) config('database.redis.metrics.poller_key', 'librenms:metrics:poller');
        $listKeys = array_values(array_unique([
            $defaultKey,
            (string) config('database.redis.metrics.services_key', $defaultKey . ':services'),
            (string) config('database.redis.metrics.discovery_key', $defaultKey . ':discovery'),
        ]));

        try {
            $redis = app('redis')->connection($connection);
            $entries = [];
            foreach ($listKeys as $listKey) {
                $entries = array_merge($entries, $redis->lrange($listKey, 0, -1));
            }
        } catch (\Throwable) {
            return [];
        }

        $payloads = [];
        foreach ($entries as $entry) {
            $payload = json_decode((string) $entry, true);
            if (! is_array($payload)) {
                continue;
            }

            $deviceId = isset($payload['device_id']) ? (int) $payload['device_id'] : 0;
            if ($deviceId <= 0) {
                continue;
            }

            if ($deviceIds !== null && ! $deviceIds->contains((string) $deviceId) && ! $deviceIds->contains($deviceId)) {
                continue;
            }

            $payloads[] = $payload;
        }

        return $payloads;
    }

    /**
     * Read latest Redis poller payload snapshot for each port interface key.
     * Snapshot key preference is device_id + ifIndex, falling back to device_id + ifName.
     *
     * @param  Collection<int, int|string>|null  $deviceIds
     * @return array<int, array{device_id: int, timestamp: int, tags: array<string, mixed>, fields: array<string, mixed>}>
     */
    private function readRedisPortPayloadSnapshots(?Collection $deviceIds = null): array
    {
        $payloads = $this->readRedisPollerPayloads($deviceIds);
        $snapshot = [];

        foreach ($payloads as $payload) {
            if (($payload['measurement'] ?? null) !== 'ports') {
                continue;
            }

            $deviceId = isset($payload['device_id']) ? (int) $payload['device_id'] : 0;
            if ($deviceId <= 0) {
                continue;
            }

            $timestamp = isset($payload['timestamp']) ? (int) $payload['timestamp'] : 0;
            $tags = is_array($payload['tags'] ?? null) ? $payload['tags'] : [];
            $fields = is_array($payload['fields'] ?? null) ? $payload['fields'] : [];

            $ifIndex = isset($tags['ifIndex']) ? (string) $tags['ifIndex'] : '';
            $ifName = isset($tags['ifName']) ? (string) $tags['ifName'] : '';
            if ($ifIndex === '' && $ifName === '') {
                continue;
            }

            $snapshotKey = $ifIndex !== ''
                ? $deviceId . ':' . $ifIndex
                : $deviceId . ':' . $ifName;

            if (! isset($snapshot[$snapshotKey]) || $timestamp >= $snapshot[$snapshotKey]['timestamp']) {
                $snapshot[$snapshotKey] = [
                    'device_id' => $deviceId,
                    'timestamp' => $timestamp,
                    'tags' => $tags,
                    'fields' => $fields,
                ];
            }
        }

        return array_values($snapshot);
    }
}
