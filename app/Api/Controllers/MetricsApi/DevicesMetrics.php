<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\Device;
use Illuminate\Http\Request;

class DevicesMetrics
{
    use Traits\MetricsHelpers;

    public function render(Request $request): string
    {
        $lines = [];

        // Determine scope (global vs detail) and parse filters
        $scope = $this->parseScope($request);
        $includeDetail = $scope === 'detail';

        // Parse filters
        $filters = $this->parseDeviceFilters($request);

        // Gather global metrics
        $totalQ = Device::query();
        $upQ = Device::query()->where('status', 1);
        $downQ = Device::query()->where('status', 0);
        $total = $this->applyDeviceFilter($totalQ, $filters['device_ids'])->count();
        $up = $this->applyDeviceFilter($upQ, $filters['device_ids'])->count();
        $down = $this->applyDeviceFilter($downQ, $filters['device_ids'])->count();

        // Append global metrics
        $this->appendMetricBlock($lines, 'librenms_devices_total', 'Total number of devices', 'gauge', ["librenms_devices_total {$total}"]);
        $this->appendMetricBlock($lines, 'librenms_devices_total_up', 'Total number of devices currently up', 'gauge', ["librenms_devices_total_up {$up}"]);
        $this->appendMetricBlock($lines, 'librenms_devices_total_down', 'Total number of devices currently down', 'gauge', ["librenms_devices_total_down {$down}"]);

        // Default to global metrics only; detailed per-access-point metrics are opt-in via ?scope=detail
        if (! $includeDetail) {
            return implode("\n", $lines) . "\n";
        }

        // Prepare per-device arrays
        $device_up_lines = [];
        $polled_timetaken_lines = [];
        $discovered_timetaken_lines = [];
        $ping_timetaken_lines = [];
        $uptime_lines = [];

        // Gather per-device metrics from Redis poller snapshots
        $payloads = $this->readRedisPollerPayloads($filters['device_ids']);

        $snapshot = [];
        foreach ($payloads as $payload) {
            $deviceId = isset($payload['device_id']) ? (int) $payload['device_id'] : 0;
            if ($deviceId <= 0) {
                continue;
            }

            $measurement = (string) ($payload['measurement'] ?? '');
            $timestamp = (int) ($payload['timestamp'] ?? 0);
            $fields = is_array($payload['fields'] ?? null) ? $payload['fields'] : [];
            $tags = is_array($payload['tags'] ?? null) ? $payload['tags'] : [];

            if (! isset($snapshot[$deviceId])) {
                $snapshot[$deviceId] = [
                    'status' => ['ts' => -1, 'value' => 0],
                    'last_polled_timetaken' => ['ts' => -1, 'value' => 0.0],
                    'last_discovered_timetaken' => ['ts' => -1, 'value' => 0.0],
                    'last_ping_timetaken' => ['ts' => -1, 'value' => 0.0],
                    'uptime' => ['ts' => -1, 'value' => 0],
                ];
            }

            if (array_key_exists('status', $payload) && $payload['status'] !== null) {
                if ($timestamp >= $snapshot[$deviceId]['status']['ts']) {
                    $snapshot[$deviceId]['status'] = ['ts' => $timestamp, 'value' => ((int) $payload['status'] > 0 ? 1 : 0)];
                }
            }

            if ($measurement === 'poller-perf' && (($tags['module'] ?? null) === 'ALL') && isset($fields['poller']) && is_numeric($fields['poller'])) {
                if ($timestamp >= $snapshot[$deviceId]['last_polled_timetaken']['ts']) {
                    $snapshot[$deviceId]['last_polled_timetaken'] = ['ts' => $timestamp, 'value' => (float) $fields['poller']];
                }
            }

            if (in_array($measurement, ['discover', 'discover-perf', 'discovery', 'discovery-perf', 'last-discovered-perf'], true)) {
                $discoverDuration = null;
                foreach (['discover', 'discovery', 'poller', 'last_discovered_timetaken'] as $fieldName) {
                    if (isset($fields[$fieldName]) && is_numeric($fields[$fieldName])) {
                        $discoverDuration = (float) $fields[$fieldName];
                        break;
                    }
                }

                if ($discoverDuration !== null && $timestamp >= $snapshot[$deviceId]['last_discovered_timetaken']['ts']) {
                    $snapshot[$deviceId]['last_discovered_timetaken'] = ['ts' => $timestamp, 'value' => $discoverDuration];
                }
            }

            if ($measurement === 'icmp-perf') {
                if (isset($fields['avg']) && is_numeric($fields['avg']) && $timestamp >= $snapshot[$deviceId]['last_ping_timetaken']['ts']) {
                    $snapshot[$deviceId]['last_ping_timetaken'] = ['ts' => $timestamp, 'value' => (float) $fields['avg']];
                }
            }

            if ($measurement === 'uptime' && isset($fields['uptime']) && is_numeric($fields['uptime'])) {
                if ($timestamp >= $snapshot[$deviceId]['uptime']['ts']) {
                    $snapshot[$deviceId]['uptime'] = ['ts' => $timestamp, 'value' => (int) $fields['uptime']];
                }
            }
        }

        $deviceIds = collect(array_keys($snapshot));
        if ($deviceIds->isEmpty()) {
            return implode("\n", $lines) . "\n";
        }

        $devices = Device::select('device_id', 'hostname', 'sysName', 'type', 'last_discovered_timetaken')
            ->whereIn('device_id', $deviceIds)
            ->get()
            ->keyBy('device_id');

        foreach ($deviceIds as $deviceId) {
            $device = $devices->get($deviceId);
            if (! $device) {
                continue;
            }

            $labels = sprintf('device_id="%s",device_hostname="%s",device_sysName="%s",device_type="%s"',
                $device->device_id,
                $this->escapeLabel((string) $device->hostname),
                $this->escapeLabel((string) $device->sysName),
                $this->escapeLabel((string) $device->type));

            $isUp = (int) $snapshot[$deviceId]['status']['value'];
            $lastPolledTimeTaken = (float) $snapshot[$deviceId]['last_polled_timetaken']['value'];
            $lastDiscoveredTimeTaken = (float) $snapshot[$deviceId]['last_discovered_timetaken']['value'];
            $lastPingTimeTaken = (float) $snapshot[$deviceId]['last_ping_timetaken']['value'];
            $uptime = (int) $snapshot[$deviceId]['uptime']['value'];

            if ($lastDiscoveredTimeTaken <= 0 && is_numeric($device->last_discovered_timetaken)) {
                $lastDiscoveredTimeTaken = (float) $device->last_discovered_timetaken;
            }

            $device_up_lines[] = "librenms_devices_up{{$labels}} {$isUp}";
            $polled_timetaken_lines[] = "librenms_devices_last_polled_timetaken_seconds{{$labels}} {$lastPolledTimeTaken}";
            $discovered_timetaken_lines[] = "librenms_devices_last_discovered_timetaken_seconds{{$labels}} {$lastDiscoveredTimeTaken}";

            $ping_timetaken_lines[] = "librenms_devices_last_ping_timetaken_seconds{{$labels}} {$lastPingTimeTaken}";
            $uptime_lines[] = "librenms_devices_uptime_seconds{{$labels}} {$uptime}";
        }

        // Append per-device metrics
        $this->appendMetricBlock($lines, 'librenms_devices_up', 'Whether a device is up (1) or not (0)', 'gauge', $device_up_lines);
        $this->appendMetricBlock($lines, 'librenms_devices_last_polled_timetaken_seconds', 'Last polled time taken in seconds', 'gauge', $polled_timetaken_lines);
        $this->appendMetricBlock($lines, 'librenms_devices_last_discovered_timetaken_seconds', 'Last discovered time taken in seconds', 'gauge', $discovered_timetaken_lines);
        $this->appendMetricBlock($lines, 'librenms_devices_last_ping_timetaken_seconds', 'Last ping time taken in seconds', 'gauge', $ping_timetaken_lines);
        $this->appendMetricBlock($lines, 'librenms_devices_uptime_seconds', 'Device uptime in seconds (0 if down)', 'gauge', $uptime_lines);

        return implode("\n", $lines) . "\n";
    }
}
