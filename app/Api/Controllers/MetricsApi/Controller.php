<?php

namespace App\Api\Controllers\MetricsApi;

use App\Models\Device;
use App\Models\AccessPoint;
use App\Models\Port;
use App\Models\PortStatistic;
use Illuminate\Http\Request;

class Controller
{
    /**
     * Prometheus metrics for devices
     * Path: /api/v0/metrics/devices
     */
    public function devices(Request $request)
    {
        $lines = [];
            
        // Gather global metrics
        $total = Device::count();
        $up = Device::where('status', 1)->count();
        $down = Device::where('status', 0)->count();


        // Append global metrics
        $lines[] = '# HELP librenms_devices_total Total number of devices';
        $lines[] = '# TYPE librenms_devices_total gauge';
        $lines[] = "librenms_devices_total {$total}";

        $lines[] = '# HELP librenms_devices_up Number of devices currently up';
        $lines[] = '# TYPE librenms_devices_up gauge';
        $lines[] = "librenms_devices_up {$up}";

        $lines[] = '# HELP librenms_devices_down Number of devices currently down';
        $lines[] = '# TYPE librenms_devices_down gauge';
        $lines[] = "librenms_devices_down {$down}";
        
        // Gather per-device metrics
        foreach (Device::select('device_id', 'hostname', 'sysName', 'type', 'status', 'last_polled_timetaken', 'last_discovered_timetaken', 'last_ping_timetaken', 'uptime')->cursor() as $device) {
            $labels = sprintf('device_id="%s",device_hostname="%s",device_sysName="%s",device_type="%s"', 
                $device->device_id,
                $this->escapeLabel((string) $device->device_hostname),
                $this->escapeLabel((string) $device->device_sysName),
                $this->escapeLabel((string) $device->device_type));

            // librenms_device_up
            $device_up_lines[] = "librenms_device_up{{$labels}} " . ($device->status ? '1' : '0');

            // librenms_last_polled_timetaken
            $lastPolledTimeTaken = $device->status ? ((int) $device->last_polled_timetaken ?: 0) : 0;
            $polled_timetaken_lines[] = "librenms_last_polled_timetaken_seconds{{$labels}} {$lastPolledTimeTaken}";

            // librenms_last_discovered_timetaken
            $lastDiscoveredTimeTaken = $device->status ? ((int) $device->last_discovered_timetaken ?: 0) : 0;
            $discovered_timetaken_lines[] = "librenms_last_discovered_timetaken_seconds{{$labels}} {$lastDiscoveredTimeTaken}";

            // librenms_last_ping_timetaken
            $lastPingTimeTaken = $device->status ? ((int) $device->last_ping_timetaken ?: 0) : 0;
            $ping_timetaken_lines[] = "librenms_last_ping_timetaken_seconds{{$labels}} {$lastPingTimeTaken}";

            // librenms_device_uptime
            $uptime = $device->status ? ((int) $device->uptime ?: 0) : 0;
            $uptime_lines[] = "librenms_device_uptime_seconds{{$labels}} {$uptime}";
        }

        // Append per-device metrics
        $lines[] = '# HELP librenms_device_up Whether a device is up (1) or not (0)';
        $lines[] = '# TYPE librenms_device_up gauge';
        $lines = array_merge($lines, $device_up_lines);

        $lines[] = '# HELP librenms_last_polled_timetaken_seconds Last polled time taken in seconds';
        $lines[] = '# TYPE librenms_last_polled_timetaken_seconds gauge';
        $lines = array_merge($lines, $polled_timetaken_lines);

        $lines[] = '# HELP librenms_last_discovered_timetaken_seconds Last discovered time taken in seconds';
        $lines[] = '# TYPE librenms_last_discovered_timetaken_seconds gauge';
        $lines = array_merge($lines, $discovered_timetaken_lines);

        $lines[] = '# HELP librenms_last_ping_timetaken_seconds Last ping time taken in seconds';
        $lines[] = '# TYPE librenms_last_ping_timetaken_seconds gauge';
        $lines = array_merge($lines, $ping_timetaken_lines);

        $lines[] = '# HELP librenms_device_uptime_seconds Device uptime in seconds (0 if down)';
        $lines[] = '# TYPE librenms_device_uptime_seconds gauge';
        $lines = array_merge($lines, $uptime_lines);

        // Combine all lines into the response body
        $body = implode("\n", $lines) . "\n";

        // Return the response with appropriate headers
        return response($body, 200, ['Content-Type' => 'text/plain; version=0.0.4; charset=utf-8']);
    }

    /**
     * Prometheus metrics for access_points
     * Path: /api/v0/metrics/access_points
     */
    public function accessPoints(Request $request)
    {
        $lines = [];

        // Gather global metrics
        $total = AccessPoint::count();

        // Append global metrics
        $lines[] = '# HELP librenms_access_points_total Total number of access points';
        $lines[] = '# TYPE librenms_access_points_total gauge';
        $lines[] = "librenms_access_points_total {$total}";

        // Prepare per-access-point metrics arrays
        $deleted_lines = [];
        $channel_lines = [];
        $txpow_lines = [];
        $radioutil_lines = [];
        $numasoclients_lines = [];
        $nummonclients_lines = [];
        $numactbssid_lines = [];
        $nummonbssid_lines = [];
        $interference_lines = [];

        // Gather device info mapping for labels
        // Limit to devices referenced by access_points
        $deviceIds = AccessPoint::select('device_id')->distinct()->pluck('device_id');
        $devices = Device::select('device_id', 'hostname', 'sysName', 'type')->whereIn('device_id', $deviceIds)->get()->keyBy('device_id');

        // Gather per-access-point metrics
        foreach (AccessPoint::select('accesspoint_id', 'device_id', 'name', 'radio_number', 'type', 'mac_addr', 'deleted', 'channel', 'txpow', 'radioutil', 'numasoclients', 'nummonclients', 'numactbssid', 'nummonbssid', 'interference')->cursor() as $ap) {
            $dev = $devices->get($ap->device_id);
            $device_hostname = $dev ? $this->escapeLabel((string) $dev->hostname) : '';
            $device_sysName = $dev ? $this->escapeLabel((string) $dev->sysName) : '';
            $device_type = $dev ? $this->escapeLabel((string) $dev->type) : '';
            $labels = sprintf('accesspoint_id="%s",device_id="%s",device_hostname="%s",device_sysName="%s",device_type="%s",name="%s",radio_number="%s",type="%s",mac_addr="%s"',
                $ap->accesspoint_id,
                $ap->device_id,
                $device_hostname,
                $device_sysName,
                $device_type,
                $this->escapeLabel((string) $ap->name),
                $this->escapeLabel((string) $ap->radio_number),
                $this->escapeLabel((string) $ap->type),
                $this->escapeLabel((string) $ap->mac_addr)
            );

            $deleted_lines[] = "librenms_access_point_deleted{{$labels}} " . ($ap->deleted ? '1' : '0');
            $channel_lines[] = "librenms_access_point_channel{{$labels}} " . ((int) $ap->channel ?: 0);
            $txpow_lines[] = "librenms_access_point_txpow{{$labels}} " . ((int) $ap->txpow ?: 0);
            $radioutil_lines[] = "librenms_access_point_radioutil{{$labels}} " . ((int) $ap->radioutil ?: 0);
            $numasoclients_lines[] = "librenms_access_point_numasoclients{{$labels}} " . ((int) $ap->numasoclients ?: 0);
            $nummonclients_lines[] = "librenms_access_point_nummonclients{{$labels}} " . ((int) $ap->nummonclients ?: 0);
            $numactbssid_lines[] = "librenms_access_point_numactbssid{{$labels}} " . ((int) $ap->numactbssid ?: 0);
            $nummonbssid_lines[] = "librenms_access_point_nummonbssid{{$labels}} " . ((int) $ap->nummonbssid ?: 0);
            $interference_lines[] = "librenms_access_point_interference{{$labels}} " . ((int) $ap->interference ?: 0);
        }

        // Append per-access-point metrics
        $lines[] = '# HELP librenms_access_point_deleted Whether an access point is deleted (1) or not (0)';
        $lines[] = '# TYPE librenms_access_point_deleted gauge';
        $lines = array_merge($lines, $deleted_lines);

        $lines[] = '# HELP librenms_access_point_channel Access point channel';
        $lines[] = '# TYPE librenms_access_point_channel gauge';
        $lines = array_merge($lines, $channel_lines);

        $lines[] = '# HELP librenms_access_point_txpow Access point transmit power';
        $lines[] = '# TYPE librenms_access_point_txpow gauge';
        $lines = array_merge($lines, $txpow_lines);

        $lines[] = '# HELP librenms_access_point_radioutil Access point radio utilization';
        $lines[] = '# TYPE librenms_access_point_radioutil gauge';
        $lines = array_merge($lines, $radioutil_lines);

        $lines[] = '# HELP librenms_access_point_numasoclients Number of associated clients (active)';
        $lines[] = '# TYPE librenms_access_point_numasoclients gauge';
        $lines = array_merge($lines, $numasoclients_lines);

        $lines[] = '# HELP librenms_access_point_nummonclients Number of monitored clients';
        $lines[] = '# TYPE librenms_access_point_nummonclients gauge';
        $lines = array_merge($lines, $nummonclients_lines);

        $lines[] = '# HELP librenms_access_point_numactbssid Number of active BSSIDs';
        $lines[] = '# TYPE librenms_access_point_numactbssid gauge';
        $lines = array_merge($lines, $numactbssid_lines);

        $lines[] = '# HELP librenms_access_point_nummonbssid Number of monitored BSSIDs';
        $lines[] = '# TYPE librenms_access_point_nummonbssid gauge';
        $lines = array_merge($lines, $nummonbssid_lines);

        $lines[] = '# HELP librenms_access_point_interference Interference level reported for access point';
        $lines[] = '# TYPE librenms_access_point_interference gauge';
        $lines = array_merge($lines, $interference_lines);

        // Combine all lines into the response body
        $body = implode("\n", $lines) . "\n";

        // Return the response with appropriate headers
        return response($body, 200, ['Content-Type' => 'text/plain; version=0.0.4; charset=utf-8']);
    }

    /**
     * Prometheus metrics for ports
     * Path: /api/v0/metrics/ports
     */
    public function ports(Request $request)
    {
        $lines = [];

        // Gather global metrics
        $total = Port::count();

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
        $poll_time_lines = [];

        // Gather device info mapping only for referenced devices
        $deviceIds = Port::select('device_id')->distinct()->pluck('device_id');
        $devices = Device::select('device_id', 'hostname', 'sysName', 'type')->whereIn('device_id', $deviceIds)->get()->keyBy('device_id');

        // Gather per-port metrics
        foreach (Port::select('port_id', 'device_id', 'ifName', 'ifDescr', 'ifIndex', 'ifType', 'ifAlias', 'ifAdminStatus', 'ifOperStatus', 'ifSpeed', 'ifInOctets', 'ifOutOctets', 'ifInUcastPkts', 'ifOutUcastPkts', 'ifInErrors', 'ifOutErrors', 'poll_time')->cursor() as $p) {
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

        // Combine all lines into the response body
        $body = implode("\n", $lines) . "\n";

        // Return the response with appropriate headers
        return response($body, 200, ['Content-Type' => 'text/plain; version=0.0.4; charset=utf-8']);
    }

    /**
     * Prometheus metrics for ports_statistics
     * Path: /api/v0/metrics/ports_statistics
     */
    public function portsStatistics(Request $request)
    {
        $lines = [];

        // Gather global metrics
        $total = PortStatistic::count();

        $lines[] = '# HELP librenms_ports_statistics_total Total number of ports_statistics rows';
        $lines[] = '# TYPE librenms_ports_statistics_total gauge';
        $lines[] = "librenms_ports_statistics_total {$total}";

        // Prepare arrays
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
        $portIds = PortStatistic::select('port_id')->pluck('port_id');
        $ports = Port::select('port_id', 'device_id', 'ifName', 'ifDescr', 'ifIndex', 'ifType', 'ifAlias')->whereIn('port_id', $portIds)->get()->keyBy('port_id');
        $deviceIds = $ports->pluck('device_id')->unique();
        $devices = Device::select('device_id', 'hostname', 'sysName', 'type')->whereIn('device_id', $deviceIds)->get()->keyBy('device_id');

        foreach (PortStatistic::cursor() as $ps) {
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

            $in_nucast_lines[] = "librenms_port_ifInNUcastPkts{{$labels}} " . ((int) $ps->ifInNUcastPkts ?: 0);
            $out_nucast_lines[] = "librenms_port_ifOutNUcastPkts{{$labels}} " . ((int) $ps->ifOutNUcastPkts ?: 0);
            $in_discards_lines[] = "librenms_port_ifInDiscards{{$labels}} " . ((int) $ps->ifInDiscards ?: 0);
            $out_discards_lines[] = "librenms_port_ifOutDiscards{{$labels}} " . ((int) $ps->ifOutDiscards ?: 0);
            $in_unknown_proto_lines[] = "librenms_port_ifInUnknownProtos{{$labels}} " . ((int) $ps->ifInUnknownProtos ?: 0);
            $in_broadcast_lines[] = "librenms_port_ifInBroadcastPkts{{$labels}} " . ((int) $ps->ifInBroadcastPkts ?: 0);
            $out_broadcast_lines[] = "librenms_port_ifOutBroadcastPkts{{$labels}} " . ((int) $ps->ifOutBroadcastPkts ?: 0);
            $in_multicast_lines[] = "librenms_port_ifInMulticastPkts{{$labels}} " . ((int) $ps->ifInMulticastPkts ?: 0);
            $out_multicast_lines[] = "librenms_port_ifOutMulticastPkts{{$labels}} " . ((int) $ps->ifOutMulticastPkts ?: 0);
        }

        $lines[] = '# HELP librenms_port_ifInNUcastPkts In non-unicast packets';
        $lines[] = '# TYPE librenms_port_ifInNUcastPkts counter';
        $lines = array_merge($lines, $in_nucast_lines);

        $lines[] = '# HELP librenms_port_ifOutNUcastPkts Out non-unicast packets';
        $lines[] = '# TYPE librenms_port_ifOutNUcastPkts counter';
        $lines = array_merge($lines, $out_nucast_lines);

        $lines[] = '# HELP librenms_port_ifInDiscards In discards';
        $lines[] = '# TYPE librenms_port_ifInDiscards counter';
        $lines = array_merge($lines, $in_discards_lines);

        $lines[] = '# HELP librenms_port_ifOutDiscards Out discards';
        $lines[] = '# TYPE librenms_port_ifOutDiscards counter';
        $lines = array_merge($lines, $out_discards_lines);

        $lines[] = '# HELP librenms_port_ifInUnknownProtos In unknown protocols';
        $lines[] = '# TYPE librenms_port_ifInUnknownProtos counter';
        $lines = array_merge($lines, $in_unknown_proto_lines);

        $lines[] = '# HELP librenms_port_ifInBroadcastPkts In broadcast packets';
        $lines[] = '# TYPE librenms_port_ifInBroadcastPkts counter';
        $lines = array_merge($lines, $in_broadcast_lines);

        $lines[] = '# HELP librenms_port_ifOutBroadcastPkts Out broadcast packets';
        $lines[] = '# TYPE librenms_port_ifOutBroadcastPkts counter';
        $lines = array_merge($lines, $out_broadcast_lines);

        $lines[] = '# HELP librenms_port_ifInMulticastPkts In multicast packets';
        $lines[] = '# TYPE librenms_port_ifInMulticastPkts counter';
        $lines = array_merge($lines, $in_multicast_lines);

        $lines[] = '# HELP librenms_port_ifOutMulticastPkts Out multicast packets';
        $lines[] = '# TYPE librenms_port_ifOutMulticastPkts counter';
        $lines = array_merge($lines, $out_multicast_lines);

        $body = implode("\n", $lines) . "\n";

        return response($body, 200, ['Content-Type' => 'text/plain; version=0.0.4; charset=utf-8']);
    }

    private function escapeLabel(string $v): string
    {
        return str_replace(["\\", '"', "\n"], ["\\\\", '\\"', '\\n'], $v);
    }
}
