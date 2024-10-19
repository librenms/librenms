<?php

namespace App\View\Components;

use App\Models\ComponentPref;
use App\Models\Device;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class QosDevice extends Component
{
    public Collection $qosGraphs;
    public string $graphType;
    public string $titlePrefName;
    public string $idPrefName;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public Device $device,
    ) {
        if ($device->os == 'routeros') {
            $this->graphType = 'device_qos_routeros';
            $this->titlePrefName = 'sq-name';
            $this->idPrefName = 'sq-name';
            $this->qosGraphs = $device->components()
                ->where('disabled', 0)
                ->where('ignore', 0)
                ->where('type', 'RouterOS-SimpleQueue')
                ->with('prefs')
                ->orderBy(ComponentPref::where('attribute', 'sq-name')
                    ->select('value'))
                ->get();
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.qos_device');
    }
}
