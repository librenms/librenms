<?php

namespace App\View\Components;

use App\Facades\DeviceCache;
use App\Models\Device;
use Illuminate\View\Component;
use LibreNMS\Util\Graph;

class DeviceLink extends Component
{
    /**
     * @var \App\Models\Device
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

    /**
     * Create a new component instance.
     *
     * @param  int|\App\Models\Device  $device
     */
    public function __construct($device, ?string $tab = null, ?string $section = null)
    {
        $this->device = $device instanceof Device ? $device : DeviceCache::get($device);
        $this->tab = $tab;
        $this->section = $section;
        $this->status = $this->status();
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
