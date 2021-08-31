<?php

namespace App\View\Components;

use App\Facades\DeviceCache;
use App\Models\Device;
use Illuminate\View\Component;

class DeviceLink extends Component
{
    /**
     * @var \App\Models\Device
     */
    public $device;
    public $graph_start;
    public $graph_end;
    public $tab;
    public $section;

    /**
     * Create a new component instance.
     *
     * @param int|\App\Models\Device $device
     */
    public function __construct($device, ?string $tab = null, ?string $section = null, ?string $graph_start = null, ?string $graph_end = null)
    {
        $this->device = $device instanceof Device ? $device : DeviceCache::get($device);
        $this->graph_start = $graph_start;
        $this->graph_end = $graph_end;
        $this->tab = $tab;
        $this->section = $section;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        if (! $this->device->canAccess(auth()->user())) {
            return view('components.device-link-no-access');
        }

        return view('components.device-link');
    }
}
