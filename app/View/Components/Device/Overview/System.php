<?php

namespace App\View\Components\Device\Overview;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use LibreNMS\Util\Time;

class System extends Component
{
    /**
     * @var array<int, array{label: string, value: mixed, title?: string}>
     */
    public array $rows;
    public string $title;
    public bool $showMap = false;
    public bool $mapOpen = false;
    public string $mapsEngine = '';
    public string $mapsApi = '';
    public bool $showDevices = false;
    public bool $showDependencies = false;
    public bool $canUpdateLocation = false;

    public function __construct(public Device $device)
    {
        $features = $device->features ? "($device->features)" : '';
        $this->title = LibrenmsConfig::get('overview_show_sysDescr')
            ? (string) $device->sysDescr
            : __('System');

        $dnsRows = $this->resolveDnsRows(); // Fetch IP from DNS resolution function

        $this->rows = array_values(array_filter([
            ['label' => __('System Name'), 'value' => $device->sysName],
            ...$dnsRows,
            ['label' => __('Description'), 'value' => $device->purpose],
            ['label' => __('Hardware'), 'value' => $device->hardware],
            ['label' => __('Operating System'), 'value' => trim(LibrenmsConfig::getOsSetting($device->os, 'text') . " $device->version $features")],
            ['label' => __('Serial'), 'value' => $device->serial],
            ['label' => __('Object ID'), 'value' => $device->sysObjectID],
            ['label' => __('Contact'), 'value' => $device->getAttrib('override_sysContact_bool') ? $device->getAttrib('override_sysContact_string') : $device->sysContact],
            $device->getAttrib('override_sysContact_bool')
                ? ['label' => __('SNMP Contact'), 'value' => $device->sysContact]
                : null,
            $this->uptimeRow(),
        ], fn (?array $row): bool => $row !== null && filled($row['value'])));

        if ($this->device->location_id && $this->device->location && $this->device->location->coordinatesValid()) {
            $this->showMap = true;
            $this->mapsApi = (string) LibrenmsConfig::get('geoloc.api_key', '');
            $this->mapsEngine = $this->mapsApi ? (string) LibrenmsConfig::get('geoloc.engine', '') : '';
            $this->mapOpen = (bool) LibrenmsConfig::get('device_location_map_open', false);
            $this->showDevices = (bool) LibrenmsConfig::get('device_location_map_show_devices', false);
            $this->showDependencies = (bool) LibrenmsConfig::get('device_location_map_show_device_dependencies', false);
            $this->canUpdateLocation = \Illuminate\Support\Facades\Gate::allows('location.update');
        }
    }

    public function render(): View|Closure|string
    {
        return view('components.device.overview.system');
    }

    /**
     * @return array{label: string, value: string}
     */
    private function uptimeRow(): array
    {
        if (! $this->device->status && ! $this->device->last_polled) {
            return ['label' => __('Uptime'), 'value' => __('Never polled')];
        }

        if ($this->device->status) {
            return ['label' => __('Uptime'), 'value' => Time::formatInterval($this->device->uptime)];
        }

        return [
            'label' => __('Downtime'),
            'value' => Time::formatInterval((int) $this->device->downSince()->diffInSeconds(null, true)),
        ];
    }

    /**
     * DNS lookup function
     *
     * @return array
     */
    private function resolveDnsRows(): array
    {
        $rows = [];

        // Honor manual IP overrides (despite do not use)
        if (! empty($this->device->overwrite_ip)) {
            $rows[] = [
                'label' => __('Assigned IP'),
                'value' => e($this->device->overwrite_ip),
            ];

            return $rows;
        }

        // Get SNMP sysName from DB
        $targetHost = trim($this->device->sysName);

        // If empty or an IP then fall back to DB Hostname
        if (empty($targetHost) || filter_var($targetHost, FILTER_VALIDATE_IP)) {
            $targetHost = trim($this->device->hostname);
        }

        // Perform lookups
        $a_records = @dns_get_record($targetHost, DNS_A);
        $aaaa_records = @dns_get_record($targetHost, DNS_AAAA);

        // Handle returned addresses if they exist
        $ipv4s = array_unique(
            array_filter(
                array_column($a_records ?: [], 'ip')
            )
        );

        if (! empty($ipv4s)) {
            $rows[] = [
                'label' => __('Resolved IPv4 (A)'),
                'value' => e(implode(', ', $ipv4s)),
            ];
        }
        
        $ipv6s = array_unique(
            array_filter(
                array_column($aaaa_records ?: [], 'ipv6')
            )
        );

        if (! empty($ipv6s)) {
            $rows[] = [
                'label' => __('Resolved IPv6 (AAAA)'),
                'value' => e(implode(', ', $ipv6s)),
            ];
        }

        // Local OS resolver fallback if upstream servers didn't find record (/etc/hosts)
        if (empty($rows) && ! empty($targetHost) && ! filter_var($targetHost, FILTER_VALIDATE_IP)) {
            $local_ip = gethostbyname($targetHost);
            if ($local_ip !== $targetHost && filter_var($local_ip, FILTER_VALIDATE_IP)) {
                $rows[] = [
                    'label' => __('Resolved IP (Local)'),
                    'value' => e($local_ip),
                ];
            }
        }

        // Final fallback to old logic if all else fails
        if (empty($rows)) {
            $fallbackIp = ! empty($this->device->ip) ? $this->device->ip : $this->device->hostname;
            $rows[] = [
                'label' => __('Resolved IP'),
                'value' => e($fallbackIp),
            ];
        }

        return $rows;
    }
}
