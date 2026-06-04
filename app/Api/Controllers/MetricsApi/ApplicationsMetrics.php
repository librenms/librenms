<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\Application;
use App\Models\ApplicationMetric;
use App\Models\Device;
use Illuminate\Http\Request;

class ApplicationsMetrics
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
        $totalQ = ApplicationMetric::query();
        // apply device filter via join
        if ($filters['device_ids']) {
            $totalQ = $totalQ->join('applications', 'application_metrics.app_id', '=', 'applications.app_id')->whereIn('applications.device_id', $filters['device_ids']->all());
        }
        $total = $totalQ->count();
        $this->appendMetricBlock($lines, 'librenms_applications_metrics_total', 'Total number of application metrics rows', 'gauge', ["librenms_applications_metrics_total {$total}"]);

        // Default to global metrics only; detailed per-application metrics are opt-in via ?scope=detail
        if (! $includeDetail) {
            return implode("\n", $lines) . "\n";
        }

        $metric_lines = [];

        // Gather per-application metrics from Redis poller snapshots
        $payloads = $this->readRedisPollerPayloads($filters['device_ids']);
        $snapshot = [];
        foreach ($payloads as $payload) {
            if (($payload['measurement'] ?? null) !== 'app') {
                continue;
            }

            $timestamp = isset($payload['timestamp']) ? (int) $payload['timestamp'] : 0;
            $tags = is_array($payload['tags'] ?? null) ? $payload['tags'] : [];
            $fields = is_array($payload['fields'] ?? null) ? $payload['fields'] : [];
            $appId = isset($tags['app_id']) ? (int) $tags['app_id'] : 0;
            if ($appId <= 0 || empty($fields)) {
                continue;
            }

            foreach ($fields as $metric => $value) {
                if (! is_numeric($value)) {
                    continue;
                }

                $snapshotKey = $appId . ':' . (string) $metric;
                if (! isset($snapshot[$snapshotKey]) || $timestamp >= $snapshot[$snapshotKey]['timestamp']) {
                    $snapshot[$snapshotKey] = [
                        'timestamp' => $timestamp,
                        'app_id' => $appId,
                        'metric' => (string) $metric,
                        'value' => (float) $value,
                    ];
                }
            }
        }

        if (empty($snapshot)) {
            return implode("\n", $lines) . "\n";
        }

        $appIds = collect(array_values(array_unique(array_map(fn (array $entry) => (int) $entry['app_id'], $snapshot))));
        $apps = Application::select('app_id', 'device_id', 'app_type', 'app_instance')
            ->whereIn('app_id', $appIds)
            ->get()
            ->keyBy('app_id');

        $deviceIds = $apps->pluck('device_id')->unique()->values();
        $devices = Device::select('device_id', 'hostname', 'sysName')->whereIn('device_id', $deviceIds)->get()->keyBy('device_id');

        foreach ($snapshot as $entry) {
            $app = $apps->get($entry['app_id']);
            if (! $app) {
                continue;
            }

            $device = $devices->get($app->device_id);
            $device_hostname = $device ? $this->escapeLabel((string) $device->hostname) : '';
            $device_sysName = $device ? $this->escapeLabel((string) $device->sysName) : '';

            $labels = sprintf(
                'app_id="%s",app_type="%s",app_instance="%s",device_id="%s",device_hostname="%s",device_sysName="%s",metric="%s"',
                $app->app_id,
                $this->escapeLabel((string) $app->app_type),
                $this->escapeLabel((string) $app->app_instance),
                $app->device_id,
                $device_hostname,
                $device_sysName,
                $this->escapeLabel((string) $entry['metric'])
            );

            $metric_lines[] = "librenms_applications_metric_value{{$labels}} " . ((float) $entry['value']);
        }

        // Append per-application metrics
        $this->appendMetricBlock($lines, 'librenms_applications_metric_value', 'Application metric value', 'gauge', $metric_lines);

        return implode("\n", $lines) . "\n";
    }
}
