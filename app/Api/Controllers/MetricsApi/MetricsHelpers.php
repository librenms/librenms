<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait MetricsHelpers
{
    /**
     * Escape a value for use in Prometheus labels
     */
    private function escapeLabel(string $v): string
    {
        return str_replace(['\\', '"', "\n"], ['\\\\', '\\"', '\\n'], $v);
    }

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

        $deviceIds = $deviceIds->filter(function ($v) {
            return $v !== null && $v !== '';
        })->unique()->values();

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
}
