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
        $this->rows = array_values(array_filter([
            ['label' => __('System Name'), 'value' => $device->sysName],
            $device->overwrite_ip
                ? ['label' => __('Assigned IP'), 'value' => $device->overwrite_ip]
                : ['label' => __('Resolved IP'), 'value' => $device->ip],
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
}
