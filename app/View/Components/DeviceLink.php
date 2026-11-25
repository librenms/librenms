<?php

namespace App\View\Components;

use App\Facades\DeviceCache;
use App\Models\Device;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Component;
use LibreNMS\Util\Graph;

class DeviceLink extends Component
{
    /**
     * @var Device
     */
    public $device;
    /**
     * @var string|null
     */
    public $tab;
    /**
     * @var string|null
     */
    public $section;
    /**
     * @var string
     */
    public $status;

    public $href;

    /**
     * Create a new component instance.
     *
     * @param  int|Device  $device
     * @param  array<string, string>  $query  extra query parameters for the link
     */
    public function __construct($device, ?string $tab = null, ?string $section = null, array $query = [])
    {
        $this->device = $device instanceof Device ? $device : DeviceCache::get($device);
        $this->tab = $tab;
        $this->section = $section;
        $this->status = $this->status();
        $route = 'device.' . ($tab ?? 'overview');
        $parameters = ['device' => $this->device->device_id ?? 1, 'section' => $section];
        if ($tab === 'routing' && $section !== null && Route::has("device.routing.$section")) {
            $route = "device.routing.$section";
            unset($parameters['section']);
        }

        $this->href = route($route, $parameters + $query);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        if (empty($this->device->device_id)) {
            return view('components.device-link-missing');
        }

        if (Gate::denies('view', $this->device)) {
            return view('components.device-link-no-access');
        }

        return view('components.device-link', [
            'graphs' => Graph::getOverviewGraphsForDevice($this->device),
        ]);
    }

    public function status(): string
    {
        if ($this->device->disabled) {
            return 'disabled';
        }

        return $this->device->status ? 'up' : ($this->device->ignore ? 'disabled' : 'down');
    }
}
