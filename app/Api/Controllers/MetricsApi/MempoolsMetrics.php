<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\Device;
use App\Models\Mempool;
use Illuminate\Http\Request;
use Traits\MetricsHelpers;

class MempoolsMetrics
{

    public function render(Request $request): string
    {
        $lines = [];

        // Parse filters
        $filters = $this->parseDeviceFilters($request);

        // Gather global metrics
        $totalQ = Mempool::query();
        $total = $this->applyDeviceFilter($totalQ, $filters['device_ids'])->count();

        // Append global metrics
        $this->appendMetricBlock($lines, 'librenms_mempools_total', 'Total number of mempools', 'gauge', "librenms_mempools_total {$total}");

        // Prepare per-mempool arrays
        $used_lines = [];
        $free_lines = [];
        $total_lines = [];
        $perc_lines = [];

        // Gather device info mapping for labels (using helper)
        $deviceIdsQuery = Mempool::select('device_id')->distinct();
        $deviceIdsQuery = $this->applyDeviceFilter($deviceIdsQuery, $filters['device_ids']);
        $deviceIds = $deviceIdsQuery->pluck('device_id');
        $devices = $this->gatherDevicesForIds($deviceIds);

        $mpQuery = Mempool::select('mempool_id', 'device_id', 'mempool_descr', 'mempool_class', 'mempool_used', 'mempool_free', 'mempool_total', 'mempool_perc');
        $mpQuery = $this->applyDeviceFilter($mpQuery, $filters['device_ids']);
        foreach ($mpQuery->cursor() as $m) {
            $dev = $devices->get($m->device_id);
            $labelsArr = [
                'mempool_id' => (string) $m->mempool_id,
                'device_id' => (string) $m->device_id,
                'device_hostname' => $dev ? $this->escapeLabel((string) $dev->hostname) : '',
                'device_sysName' => $dev ? $this->escapeLabel((string) $dev->sysName) : '',
                'mempool_descr' => $this->escapeLabel((string) $m->mempool_descr),
                'mempool_class' => $this->escapeLabel((string) $m->mempool_class),
            ];

            $labels = $this->formatLabels($labelsArr);

            $used_lines[] = "librenms_mempool_used_bytes{{$labels}} " . ((int) $m->mempool_used ?: 0);
            $free_lines[] = "librenms_mempool_free_bytes{{$labels}} " . ((int) $m->mempool_free ?: 0);
            $total_lines[] = "librenms_mempool_total_bytes{{$labels}} " . ((int) $m->mempool_total ?: 0);
            $perc_lines[] = "librenms_mempool_used_percent{{$labels}} " . ((int) $m->mempool_perc ?: 0);
        }

        // Append per-mempool metrics
        $this->appendMetricBlock($lines, 'librenms_mempool_used_bytes', 'Used bytes in mempool', 'gauge', $used_lines);
        $this->appendMetricBlock($lines, 'librenms_mempool_free_bytes', 'Free bytes in mempool', 'gauge', $free_lines);
        $this->appendMetricBlock($lines, 'librenms_mempool_total_bytes', 'Total bytes in mempool', 'gauge', $total_lines);
        $this->appendMetricBlock($lines, 'librenms_mempool_used_percent', 'Percent used', 'gauge', $perc_lines);

        return implode("\n", $lines) . "\n";
    }
}
