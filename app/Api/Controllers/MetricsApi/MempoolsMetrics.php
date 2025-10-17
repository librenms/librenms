<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\Mempool;
use App\Models\Device;
use Illuminate\Http\Request;

class MempoolsMetrics
{
    use MetricsHelpers;

    public function render(Request $request): string
    {
        $lines = [];

        // Parse filters
        $filters = $this->parseDeviceFilters($request);

        // Gather global metrics
        $totalQ = Mempool::query();
        $total = $this->applyDeviceFilter($totalQ, $filters['device_ids'])->count();

        // Prepare per-mempool metrics arrays
        $lines[] = '# HELP librenms_mempools_total Total number of mempools';
        $lines[] = '# TYPE librenms_mempools_total gauge';
        $lines[] = "librenms_mempools_total {$total}";

        $used_lines = [];
        $free_lines = [];
        $total_lines = [];
        $perc_lines = [];

        // Gather device info mapping for labels
        $deviceIdsQuery = Mempool::select('device_id')->distinct();
        $deviceIdsQuery = $this->applyDeviceFilter($deviceIdsQuery, $filters['device_ids']);
        $deviceIds = $deviceIdsQuery->pluck('device_id');
        $devices = Device::select('device_id', 'hostname', 'sysName', 'type')->whereIn('device_id', $deviceIds)->get()->keyBy('device_id');

        $mpQuery = Mempool::select('mempool_id', 'device_id', 'mempool_descr', 'mempool_class', 'mempool_used', 'mempool_free', 'mempool_total', 'mempool_perc');
        $mpQuery = $this->applyDeviceFilter($mpQuery, $filters['device_ids']);
        foreach ($mpQuery->cursor() as $m) {
            $dev = $devices->get($m->device_id);
            $device_hostname = $dev ? $this->escapeLabel((string) $dev->hostname) : '';
            $device_sysName = $dev ? $this->escapeLabel((string) $dev->sysName) : '';
            $labels = sprintf('mempool_id="%s",device_id="%s",device_hostname="%s",device_sysName="%s",mempool_descr="%s",mempool_class="%s"',
                $m->mempool_id,
                $m->device_id,
                $device_hostname,
                $device_sysName,
                $this->escapeLabel((string) $m->mempool_descr),
                $this->escapeLabel((string) $m->mempool_class)
            );

            $used_lines[] = "librenms_mempool_used_bytes{{$labels}} " . ((int) $m->mempool_used ?: 0);
            $free_lines[] = "librenms_mempool_free_bytes{{$labels}} " . ((int) $m->mempool_free ?: 0);
            $total_lines[] = "librenms_mempool_total_bytes{{$labels}} " . ((int) $m->mempool_total ?: 0);
            $perc_lines[] = "librenms_mempool_used_percent{{$labels}} " . ((int) $m->mempool_perc ?: 0);
        }

        // Append per-mempool metrics
        $lines[] = '# HELP librenms_mempool_used_bytes Used bytes in mempool';
        $lines[] = '# TYPE librenms_mempool_used_bytes gauge';
        $lines = array_merge($lines, $used_lines);

        $lines[] = '# HELP librenms_mempool_free_bytes Free bytes in mempool';
        $lines[] = '# TYPE librenms_mempool_free_bytes gauge';
        $lines = array_merge($lines, $free_lines);

        $lines[] = '# HELP librenms_mempool_total_bytes Total bytes in mempool';
        $lines[] = '# TYPE librenms_mempool_total_bytes gauge';
        $lines = array_merge($lines, $total_lines);

        $lines[] = '# HELP librenms_mempool_used_percent Percent used';
        $lines[] = '# TYPE librenms_mempool_used_percent gauge';
        $lines = array_merge($lines, $perc_lines);

        return implode("\n", $lines) . "\n";
    }
}
