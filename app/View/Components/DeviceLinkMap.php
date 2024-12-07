<?php

namespace App\View\Components;

use App\Facades\DeviceCache;
use App\Models\Device;
use Illuminate\View\Component;
use LibreNMS\Util\Graph;

class DeviceLinkMap extends Component
{
    /**
     * @var \App\Models\Device
     */
    public $device;

    /**
     * Create a new component instance.
     *
     * @param  int|\App\Models\Device  $device
     */
    public function __construct($device)
    {
        $this->device = $device instanceof Device ? $device : DeviceCache::get($device);
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

        if (! $this->device->canAccess(auth()->user())) {
            return view('components.device-link-no-access');
        }

        return view('components.device-link-map', [
            'graphs' => Graph::getOverviewGraphsForDevice($this->device),
        ]);
    }
}
