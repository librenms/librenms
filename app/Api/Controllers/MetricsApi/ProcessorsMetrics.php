<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\Device;
use App\Models\Processor;
use Illuminate\Http\Request;

class ProcessorsMetrics
{
    use MetricsHelpers;

    public function render(Request $request): string
    {
        $lines = [];

        // Parse filters
        $filters = $this->parseDeviceFilters($request);

        // Gather global metrics
        $totalQ = Processor::query();
        $total = $this->applyDeviceFilter($totalQ, $filters['device_ids'])->count();

        // Append global metrics
        $lines[] = '# HELP librenms_processors_total Total number of processors';
        $lines[] = '# TYPE librenms_processors_total gauge';
        $lines[] = "librenms_processors_total {$total}";

        $usage_lines = [];

        // Gather device info mapping for labels
        $deviceIdsQuery = Processor::select('device_id')->distinct();
        $deviceIdsQuery = $this->applyDeviceFilter($deviceIdsQuery, $filters['device_ids']);
        $deviceIds = $deviceIdsQuery->pluck('device_id');
        $devices = Device::select('device_id', 'hostname', 'sysName', 'type')->whereIn('device_id', $deviceIds)->get()->keyBy('device_id');

        $procQuery = Processor::select('processor_id', 'device_id', 'processor_descr', 'processor_index', 'processor_type', 'processor_usage');
        $procQuery = $this->applyDeviceFilter($procQuery, $filters['device_ids']);
        foreach ($procQuery->cursor() as $p) {
            $dev = $devices->get($p->device_id);
            $device_hostname = $dev ? $this->escapeLabel((string) $dev->hostname) : '';
            $device_sysName = $dev ? $this->escapeLabel((string) $dev->sysName) : '';

            $labels = sprintf('processor_id="%s",device_id="%s",device_hostname="%s",device_sysName="%s",processor_descr="%s",processor_index="%s",processor_type="%s"',
                $p->processor_id,
                $p->device_id,
                $device_hostname,
                $device_sysName,
                $this->escapeLabel((string) $p->processor_descr),
                $this->escapeLabel((string) $p->processor_index),
                $this->escapeLabel((string) $p->processor_type)
            );

            $usage_lines[] = "librenms_processor_usage_percent{{$labels}} " . ((int) $p->processor_usage ?: 0);
        }

        // Append per-processor metrics
        $lines[] = '# HELP librenms_processor_usage_percent Processor usage percent (0-100)';
        $lines[] = '# TYPE librenms_processor_usage_percent gauge';
        $lines = array_merge($lines, $usage_lines);

        return implode("\n", $lines) . "\n";
    }
}
