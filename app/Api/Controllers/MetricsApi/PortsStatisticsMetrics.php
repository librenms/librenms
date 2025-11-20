<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\Device;
use App\Models\Port;
use App\Models\PortStatistic;
use Illuminate\Http\Request;

class PortsStatisticsMetrics
{
    use Traits\MetricsHelpers;

    public function render(Request $request): string
    {
        $lines = [];

        // Parse filters
        $filters = $this->parseDeviceFilters($request);

        // Gather global metrics
        if ($filters['device_ids']) {
            // PortStatistic doesn't have device_id; translate device_ids -> port_ids
            $portIdsForFilter = Port::whereIn('device_id', $filters['device_ids']->all())->pluck('port_id');
            $total = PortStatistic::whereIn('port_id', $portIdsForFilter)->count();
        } else {
            $total = PortStatistic::count();
        }

        // Append global metrics
        $this->appendMetricBlock($lines, 'librenms_ports_statistics_total', 'Total number of ports_statistics rows', 'gauge', [$total]);

        // Prepare per-port stats arrays
        $in_nucast_lines = [];
        $out_nucast_lines = [];
        $in_discards_lines = [];
        $out_discards_lines = [];
        $in_unknown_proto_lines = [];
        $in_broadcast_lines = [];
        $out_broadcast_lines = [];
        $in_multicast_lines = [];
        $out_multicast_lines = [];

        // Preload device/port labels mapping
        if ($filters['device_ids']) {
            $portIds = Port::whereIn('device_id', $filters['device_ids']->all())->pluck('port_id');
        } else {
            $portIds = PortStatistic::select('port_id')->distinct()->pluck('port_id');
        }
        $ports = Port::select('port_id', 'device_id', 'ifName', 'ifDescr', 'ifIndex', 'ifType', 'ifAlias')->whereIn('port_id', $portIds)->get()->keyBy('port_id');
        $deviceIds = $ports->pluck('device_id')->unique();
        $devices = Device::select('device_id', 'hostname', 'sysName', 'type')->whereIn('device_id', $deviceIds)->get()->keyBy('device_id');

        if ($filters['device_ids']) {
            $psQuery = PortStatistic::whereIn('port_id', $portIds);
        } else {
            $psQuery = PortStatistic::query();
        }
        foreach ($psQuery->cursor() as $ps) {
            $p = $ports->get($ps->port_id);
            $dev = $p ? $devices->get($p->device_id) : null;
            $device_hostname = $dev ? $this->escapeLabel((string) $dev->hostname) : '';
            $device_sysName = $dev ? $this->escapeLabel((string) $dev->sysName) : '';
            $device_type = $dev ? $this->escapeLabel((string) $dev->type) : '';

            $labels = sprintf('port_id="%s",device_id="%s",device_hostname="%s",device_sysName="%s",device_type="%s",ifName="%s",ifDescr="%s",ifIndex="%s",ifType="%s",ifAlias="%s"',
                $ps->port_id,
                $p ? $p->device_id : '',
                $device_hostname,
                $device_sysName,
                $device_type,
                $this->escapeLabel((string) ($p->ifName ?? '')),
                $this->escapeLabel((string) ($p->ifDescr ?? '')),
                $this->escapeLabel((string) ($p->ifIndex ?? '')),
                $this->escapeLabel((string) ($p->ifType ?? '')),
                $this->escapeLabel((string) ($p->ifAlias ?? ''))
            );

            $in_nucast_lines[] = "librenms_ports_statistics_ifInNUcastPkts{{$labels}} " . ((int) $ps->ifInNUcastPkts ?: 0);
            $out_nucast_lines[] = "librenms_ports_statistics_ifOutNUcastPkts{{$labels}} " . ((int) $ps->ifOutNUcastPkts ?: 0);
            $in_discards_lines[] = "librenms_ports_statistics_ifInDiscards{{$labels}} " . ((int) $ps->ifInDiscards ?: 0);
            $out_discards_lines[] = "librenms_ports_statistics_ifOutDiscards{{$labels}} " . ((int) $ps->ifOutDiscards ?: 0);
            $in_unknown_proto_lines[] = "librenms_ports_statistics_ifInUnknownProtos{{$labels}} " . ((int) $ps->ifInUnknownProtos ?: 0);
            $in_broadcast_lines[] = "librenms_ports_statistics_ifInBroadcastPkts{{$labels}} " . ((int) $ps->ifInBroadcastPkts ?: 0);
            $out_broadcast_lines[] = "librenms_ports_statistics_ifOutBroadcastPkts{{$labels}} " . ((int) $ps->ifOutBroadcastPkts ?: 0);
            $in_multicast_lines[] = "librenms_ports_statistics_ifInMulticastPkts{{$labels}} " . ((int) $ps->ifInMulticastPkts ?: 0);
            $out_multicast_lines[] = "librenms_ports_statistics_ifOutMulticastPkts{{$labels}} " . ((int) $ps->ifOutMulticastPkts ?: 0);
        }

        // Append per-port metrics
        $this->appendMetricBlock($lines, 'librenms_ports_statistics_ifInNUcastPkts', 'In non-unicast packets', 'counter', $in_nucast_lines);
        $this->appendMetricBlock($lines, 'librenms_ports_statistics_ifOutNUcastPkts', 'Out non-unicast packets', 'counter', $out_nucast_lines);
        $this->appendMetricBlock($lines, 'librenms_ports_statistics_ifInDiscards', 'In discards', 'counter', $in_discards_lines);
        $this->appendMetricBlock($lines, 'librenms_ports_statistics_ifOutDiscards', 'Out discards', 'counter', $out_discards_lines);
        $this->appendMetricBlock($lines, 'librenms_ports_statistics_ifInUnknownProtos', 'In unknown protocols', 'counter', $in_unknown_proto_lines);
        $this->appendMetricBlock($lines, 'librenms_ports_statistics_ifInBroadcastPkts', 'In broadcast packets', 'counter', $in_broadcast_lines);
        $this->appendMetricBlock($lines, 'librenms_ports_statistics_ifOutBroadcastPkts', 'Out broadcast packets', 'counter', $out_broadcast_lines);
        $this->appendMetricBlock($lines, 'librenms_ports_statistics_ifInMulticastPkts', 'In multicast packets', 'counter', $in_multicast_lines);
        $this->appendMetricBlock($lines, 'librenms_ports_statistics_ifOutMulticastPkts', 'Out multicast packets', 'counter', $out_multicast_lines);

        return implode("\n", $lines) . "\n";
    }
}
