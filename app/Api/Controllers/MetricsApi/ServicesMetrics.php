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

        // Gather global metrics
        $total = Service::count();

        // Append global metrics
        $this->appendMetricBlock($lines, 'librenms_services_total', 'Total number of services configured', 'gauge', [$total]);

        // counts by status (0=OK,1=WARNING,2=CRITICAL)
        $status_lines = [];
        $statuses = Service::select('service_status', DB::raw('count(*) as cnt'))->groupBy('service_status')->get();
        /** @var \stdClass $s */
        foreach ($statuses as $s) {
            $status_lines[] = sprintf('librenms_services_by_status{status="%s"} %d', $s->service_status, $s->cnt);
        }
        $this->appendMetricBlock($lines, 'librenms_services_by_status', 'Number of services by status (0=OK,1=WARNING,2=CRITICAL)', 'gauge', $status_lines);

        // Ignored Service count
        $ignored = Service::where('service_ignore', 1)->count();
        $this->appendMetricBlock($lines, 'librenms_services_ignored', 'Number of ignored services', 'gauge', [$ignored]);

        // Disabled Service count
        $disabled = Service::where('service_disabled', 1)->count();
        $this->appendMetricBlock($lines, 'librenms_services_disabled', 'Number of disabled services', 'gauge', [$disabled]);

        // Prepare per-device counts by status (may be high-cardinality)
        $deviceIds = Service::select('device_id')->distinct()->pluck('device_id');
        $devices = Device::select('device_id', 'hostname', 'sysName')->whereIn('device_id', $deviceIds)->get()->keyBy('device_id');

        $services_lines = [];
        $rows = Service::select('device_id', 'service_status', DB::raw('count(*) as cnt'))->groupBy('device_id', 'service_status')->cursor();
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
