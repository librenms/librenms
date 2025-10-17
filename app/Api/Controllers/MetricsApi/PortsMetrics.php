<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\Device;
use App\Models\Port;
use Illuminate\Http\Request;

class PortsMetrics
{
    use MetricsHelpers;

    public function render(Request $request): string
    {
        $lines = [];

        // Parse filters
        $filters = $this->parseDeviceFilters($request);

        // Gather global metrics
        $totalQ = Port::query();
        $total = $this->applyDeviceFilter($totalQ, $filters['device_ids'])->count();

        // Append global metrics
        $lines[] = '# HELP librenms_ports_total Total number of ports';
        $lines[] = '# TYPE librenms_ports_total gauge';
        $lines[] = "librenms_ports_total {$total}";

        // Prepare per-port metric arrays
        $admin_lines = [];
        $oper_lines = [];
        $speed_lines = [];
        $in_octets_lines = [];
        $out_octets_lines = [];
        $in_ucast_pkt_lines = [];
        $out_ucast_pkt_lines = [];
        $in_errors_lines = [];
        $out_errors_lines = [];

        // Gather device info mapping only for referenced devices
        $deviceIdsQuery = Port::select('device_id')->distinct();
        $deviceIdsQuery = $this->applyDeviceFilter($deviceIdsQuery, $filters['device_ids']);
        $deviceIds = $deviceIdsQuery->pluck('device_id');
        $devices = Device::select('device_id', 'hostname', 'sysName', 'type')->whereIn('device_id', $deviceIds)->get()->keyBy('device_id');

        // Gather per-port metrics
        $portQuery = Port::select('port_id', 'device_id', 'ifName', 'ifDescr', 'ifIndex', 'ifType', 'ifAlias', 'ifAdminStatus', 'ifOperStatus', 'ifSpeed', 'ifInOctets', 'ifOutOctets', 'ifInUcastPkts', 'ifOutUcastPkts', 'ifInErrors', 'ifOutErrors', 'poll_time');
        $portQuery = $this->applyDeviceFilter($portQuery, $filters['device_ids']);
        foreach ($portQuery->cursor() as $p) {
            $dev = $devices->get($p->device_id);
            $device_hostname = $dev ? $this->escapeLabel((string) $dev->hostname) : '';
            $device_sysName = $dev ? $this->escapeLabel((string) $dev->sysName) : '';
            $device_type = $dev ? $this->escapeLabel((string) $dev->type) : '';

            $labels = sprintf('port_id="%s",device_id="%s",device_hostname="%s",device_sysName="%s",device_type="%s",ifName="%s",ifDescr="%s",ifIndex="%s",ifType="%s",ifAlias="%s"',
                $p->port_id,
                $p->device_id,
                $device_hostname,
                $device_sysName,
                $device_type,
                $this->escapeLabel((string) $p->ifName),
                $this->escapeLabel((string) $p->ifDescr),
                $this->escapeLabel((string) $p->ifIndex),
                $this->escapeLabel((string) $p->ifType),
                $this->escapeLabel((string) $p->ifAlias)
            );

            $admin_lines[] = "librenms_port_admin_up{{$labels}} " . ($p->ifAdminStatus === 'up' ? '1' : '0');
            $oper_lines[] = "librenms_port_oper_up{{$labels}} " . ($p->ifOperStatus === 'up' ? '1' : '0');
            $speed_lines[] = "librenms_port_speed_bits_per_second{{$labels}} " . ((int) $p->ifSpeed ?: 0);
            $in_octets_lines[] = "librenms_port_ifInOctets{{$labels}} " . ((int) $p->ifInOctets ?: 0);
            $out_octets_lines[] = "librenms_port_ifOutOctets{{$labels}} " . ((int) $p->ifOutOctets ?: 0);
            $in_ucast_pkt_lines[] = "librenms_port_ifInUcastPkts{{$labels}} " . ((int) $p->ifInUcastPkts ?: 0);
            $out_ucast_pkt_lines[] = "librenms_port_ifOutUcastPkts{{$labels}} " . ((int) $p->ifOutUcastPkts ?: 0);
            $in_errors_lines[] = "librenms_port_ifInErrors{{$labels}} " . ((int) $p->ifInErrors ?: 0);
            $out_errors_lines[] = "librenms_port_ifOutErrors{{$labels}} " . ((int) $p->ifOutErrors ?: 0);
        }

        // Append metrics
        $lines[] = '# HELP librenms_port_admin_up Whether admin status is up (1) or not (0)';
        $lines[] = '# TYPE librenms_port_admin_up gauge';
        $lines = array_merge($lines, $admin_lines);

        $lines[] = '# HELP librenms_port_oper_up Whether oper status is up (1) or not (0)';
        $lines[] = '# TYPE librenms_port_oper_up gauge';
        $lines = array_merge($lines, $oper_lines);

        $lines[] = '# HELP librenms_port_speed_bits_per_second Port speed in bits per second';
        $lines[] = '# TYPE librenms_port_speed_bits_per_second gauge';
        $lines = array_merge($lines, $speed_lines);

        $lines[] = '# HELP librenms_port_ifInOctets In octets';
        $lines[] = '# TYPE librenms_port_ifInOctets counter';
        $lines = array_merge($lines, $in_octets_lines);

        $lines[] = '# HELP librenms_port_ifOutOctets Out octets';
        $lines[] = '# TYPE librenms_port_ifOutOctets counter';
        $lines = array_merge($lines, $out_octets_lines);

        $lines[] = '# HELP librenms_port_ifInUcastPkts In unicast packets';
        $lines[] = '# TYPE librenms_port_ifInUcastPkts counter';
        $lines = array_merge($lines, $in_ucast_pkt_lines);

        $lines[] = '# HELP librenms_port_ifOutUcastPkts Out unicast packets';
        $lines[] = '# TYPE librenms_port_ifOutUcastPkts counter';
        $lines = array_merge($lines, $out_ucast_pkt_lines);

        $lines[] = '# HELP librenms_port_ifInErrors In errors';
        $lines[] = '# TYPE librenms_port_ifInErrors counter';
        $lines = array_merge($lines, $in_errors_lines);

        $lines[] = '# HELP librenms_port_ifOutErrors Out errors';
        $lines[] = '# TYPE librenms_port_ifOutErrors counter';
        $lines = array_merge($lines, $out_errors_lines);

        return implode("\n", $lines) . "\n";
    }
}
