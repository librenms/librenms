<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\Device;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServicesMetrics
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
        $totalQ = Service::query();
        $total = $this->applyDeviceFilter($totalQ, $filters['device_ids'])->count();

        // Append global metrics
        $this->appendMetricBlock($lines, 'librenms_services_total', 'Total number of services configured', 'gauge', ["librenms_services_total {$total}"]);

        // Default to global metrics only; detailed per-access-point metrics are opt-in via ?scope=detail
        if (! $includeDetail) {
            return implode("\n", $lines) . "\n";
        }

        // counts by status (0=OK,1=WARNING,2=CRITICAL)
        $status_lines = [];
        $statusesQ = Service::select('service_status', DB::raw('count(*) as cnt'));
        $statuses = $this->applyDeviceFilter($statusesQ, $filters['device_ids'])->groupBy('service_status')->get();
        /** @var \stdClass $s */
        foreach ($statuses as $s) {
            $status_lines[] = sprintf('librenms_services_by_status{status="%s"} %d', $s->service_status, $s->cnt);
        }
        $this->appendMetricBlock($lines, 'librenms_services_by_status', 'Number of services by status (0=OK,1=WARNING,2=CRITICAL)', 'gauge', $status_lines);

        // Ignored Service count
        $ignoredQ = Service::where('service_ignore', 1);
        $ignored = $this->applyDeviceFilter($ignoredQ, $filters['device_ids'])->count();
        $this->appendMetricBlock($lines, 'librenms_services_ignored', 'Number of ignored services', 'gauge', ["librenms_services_ignored {$ignored}"]);

        // Disabled Service count
        $disabledQ = Service::where('service_disabled', 1);
        $disabled = $this->applyDeviceFilter($disabledQ, $filters['device_ids'])->count();
        $this->appendMetricBlock($lines, 'librenms_services_disabled', 'Number of disabled services', 'gauge', ["librenms_services_disabled {$disabled}"]);

        // Per-service values sourced from Redis service poller snapshots
        $service_metric_lines = [];
        $payloads = $this->readRedisPollerPayloads($filters['device_ids']);
        $snapshot = [];
        foreach ($payloads as $payload) {
            if (($payload['measurement'] ?? null) !== 'services') {
                continue;
            }

            $timestamp = isset($payload['timestamp']) ? (int) $payload['timestamp'] : 0;
            $tags = is_array($payload['tags'] ?? null) ? $payload['tags'] : [];
            $fields = is_array($payload['fields'] ?? null) ? $payload['fields'] : [];
            $serviceId = isset($tags['service_id']) ? (int) $tags['service_id'] : 0;
            if ($serviceId <= 0 || empty($fields)) {
                continue;
            }

            foreach ($fields as $metric => $value) {
                if (! is_numeric($value)) {
                    continue;
                }

                $snapshotKey = $serviceId . ':' . (string) $metric;
                if (! isset($snapshot[$snapshotKey]) || $timestamp >= $snapshot[$snapshotKey]['timestamp']) {
                    $snapshot[$snapshotKey] = [
                        'timestamp' => $timestamp,
                        'service_id' => $serviceId,
                        'metric' => (string) $metric,
                        'value' => (float) $value,
                    ];
                }
            }
        }

        if (! empty($snapshot)) {
            $serviceIds = collect(array_values(array_unique(array_map(fn (array $entry) => (int) $entry['service_id'], $snapshot))));
            $services = Service::select('service_id', 'device_id', 'service_type', 'service_name', 'service_desc', 'service_status')
                ->whereIn('service_id', $serviceIds)
                ->get()
                ->keyBy('service_id');
            $devices = Device::select('device_id', 'hostname', 'sysName')
                ->whereIn('device_id', $services->pluck('device_id')->unique()->values())
                ->get()
                ->keyBy('device_id');

            foreach ($snapshot as $entry) {
                $service = $services->get($entry['service_id']);
                if (! $service) {
                    continue;
                }

                $device = $devices->get($service->device_id);
                $device_hostname = $device ? $this->escapeLabel((string) $device->hostname) : '';
                $device_sysName = $device ? $this->escapeLabel((string) $device->sysName) : '';
                $labels = sprintf(
                    'service_id="%s",service_type="%s",service_name="%s",service_desc="%s",service_status="%s",device_id="%s",device_hostname="%s",device_sysName="%s",metric="%s"',
                    $service->service_id,
                    $this->escapeLabel((string) $service->service_type),
                    $this->escapeLabel((string) $service->service_name),
                    $this->escapeLabel((string) $service->service_desc),
                    $service->service_status,
                    $service->device_id,
                    $device_hostname,
                    $device_sysName,
                    $this->escapeLabel((string) $entry['metric'])
                );
                $service_metric_lines[] = "librenms_services_metric_value{{$labels}} {$entry['value']}";
            }
        }

        $this->appendMetricBlock($lines, 'librenms_services_metric_value', 'Latest per-service metric values from Redis service poller data', 'gauge', $service_metric_lines);

        // Prepare per-device counts by status (may be high-cardinality)
        $deviceIdsQuery = Service::select('device_id')->distinct();
        $deviceIds = $this->applyDeviceFilter($deviceIdsQuery, $filters['device_ids'])->pluck('device_id');
        $devices = Device::select('device_id', 'hostname', 'sysName')->whereIn('device_id', $deviceIds)->get()->keyBy('device_id');

        $services_lines = [];
        $rowsQ = Service::select('device_id', 'service_status', DB::raw('count(*) as cnt'));
        $rows = $this->applyDeviceFilter($rowsQ, $filters['device_ids'])->groupBy('device_id', 'service_status')->cursor();
        /** @var \stdClass $r */
        foreach ($rows as $r) {
            $dev = $devices->get($r->device_id);
            $device_hostname = $dev ? $this->escapeLabel((string) $dev->hostname) : '';
            $device_sysName = $dev ? $this->escapeLabel((string) $dev->sysName) : '';
            $labels = sprintf('device_id="%s",device_hostname="%s",device_sysName="%s",status="%s"',
                $r->device_id,
                $device_hostname,
                $device_sysName,
                $r->service_status
            );
            $services_lines[] = "librenms_services_by_device_and_status{{$labels}} {$r->cnt}";
        }

        // Append per-services by device metrics
        $this->appendMetricBlock($lines, 'librenms_services_by_device_and_status', 'Number of services per device by status', 'gauge', $services_lines);

        return implode("\n", $lines) . "\n";
    }
}
