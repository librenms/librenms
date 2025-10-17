<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\ApplicationMetric;
use App\Models\Application;
use App\Models\Device;
use Illuminate\Http\Request;

class ApplicationsMetrics
{
    use MetricsHelpers;

    public function render(Request $request): string
    {
        $lines = [];

        // Gather global metrics
        $total = ApplicationMetric::count();

        // Append global metrics
        $lines[] = '# HELP librenms_application_metrics_total Total number of application metrics rows';
        $lines[] = '# TYPE librenms_application_metrics_total gauge';
        $lines[] = "librenms_application_metrics_total {$total}";

        $metric_lines = [];

        // Get device ids referenced by application metrics so we can preload devices
        $deviceIds = Application::join('application_metrics', 'applications.app_id', '=', 'application_metrics.app_id')
            ->distinct()
            ->pluck('applications.device_id');

        $devices = Device::select('device_id', 'hostname', 'sysName')->whereIn('device_id', $deviceIds)->get()->keyBy('device_id');

        // join application_metrics -> applications to get app metadata alongside metric rows
        $query = ApplicationMetric::join('applications', 'application_metrics.app_id', '=', 'applications.app_id')
            ->select(
                'application_metrics.id',
                'application_metrics.app_id',
                'application_metrics.metric',
                'application_metrics.value',
                'applications.device_id',
                'applications.app_type',
                'applications.app_instance'
            );

        foreach ($query->cursor() as $am) {
            $device = $devices->get($am->device_id);
            $device_hostname = $device ? $this->escapeLabel((string) $device->hostname) : '';
            $device_sysName = $device ? $this->escapeLabel((string) $device->sysName) : '';

            $labels = sprintf(
                'app_id="%s",app_type="%s",app_instance="%s",device_id="%s",device_hostname="%s",device_sysName="%s",metric="%s"',
                $am->app_id,
                $this->escapeLabel((string) $am->app_type),
                $this->escapeLabel((string) $am->app_instance),
                $am->device_id,
                $device_hostname,
                $device_sysName,
                $this->escapeLabel((string) $am->metric)
            );

            $metric_lines[] = "librenms_application_metric_value{{$labels}} " . ((float) $am->value ?: 0);
        }

        // Append per-application metrics
        $lines[] = '# HELP librenms_application_metric_value Application metric value';
        $lines[] = '# TYPE librenms_application_metric_value gauge';
        $lines = array_merge($lines, $metric_lines);

        return implode("\n", $lines) . "\n";
    }
}
