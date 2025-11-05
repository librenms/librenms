<?php

namespace App\Api\Controllers\MetricsApi\Traits;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait MetricsHelpers
{
    /**
     * Parse device / device group filters from the Request.
     * Supports:
     * - device_id (single), device_ids (comma-separated)
     * - hostname (single), hostnames (comma-separated)
     * - device_group (id or name) which will expand to device ids in that group
     * Returns an array with key 'device_ids' => Collection|null
     */
    private function parseDeviceFilters(Request $request): array
    {
        $deviceIds = collect();

        // single or comma-separated device ids
        if ($request->filled('device_id')) {
            $deviceIds = $deviceIds->merge(array_map('trim', explode(',', $request->get('device_id'))));
        }
        if ($request->filled('device_ids')) {
            $deviceIds = $deviceIds->merge(array_map('trim', explode(',', $request->get('device_ids'))));
        }

        // hostname(s)
        $hostnames = collect();
        if ($request->filled('hostname')) {
            $hostnames = $hostnames->merge(array_map('trim', explode(',', $request->get('hostname'))));
        }
        if ($request->filled('hostnames')) {
            $hostnames = $hostnames->merge(array_map('trim', explode(',', $request->get('hostnames'))));
        }
        if ($hostnames->isNotEmpty()) {
            $fromHost = Device::whereIn('hostname', $hostnames)->orWhereIn('sysName', $hostnames)->pluck('device_id');
            $deviceIds = $deviceIds->merge($fromHost);
        }

        // device_group may be id (numeric) or name; expand to device ids
        if ($request->filled('device_group')) {
            $dg = trim($request->get('device_group'));
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
     */
    private function applyDeviceFilter($query, ?Collection $deviceIds)
    {
        if ($deviceIds === null) {
            return $query;
        }

        return $query->whereIn((string) ($query->getModel() ? $query->getModel()->getTable() . '.device_id' : 'device_id'), $deviceIds->all());
    }

    /**
     * Given a collection of device ids, return a keyed map of Device models by device_id.
     * If null is given, returns an empty collection.
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
}
