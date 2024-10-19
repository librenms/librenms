<?php

namespace App\View\Components;

use App\Models\ComponentPref;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class QosPort extends Component
{
    public Collection $qosPortGraphs;
    public Collection $qosDeviceGraphs;
    public string $graphType;
    public string $titlePrefName;
    public string $graphidPrefName;
    public string $qosidPrefName = '';

    /**
     * Create a new component instance.
     */
    public function __construct(
        public array $device,
        public int $portId,
        public int $parentId,
        public Collection $deviceGraphs,
    ) {
        if ($device['os'] == 'routeros') {
            $this->graphType = 'port_qos_routeros';
            $this->titlePrefName = 'qt-name';
            $this->graphidPrefName = 'qt-name';
            $this->qosidPrefName = 'qt-id';
            if ($deviceGraphs->count()) {
                $this->qosDeviceGraphs = $deviceGraphs;
            } else {
                $this->qosDeviceGraphs = \App\Models\Component::where('device_id', $device['device_id'])
                    ->where('disabled', 0)
                    ->where('ignore', 0)
                    ->where('type', 'RouterOS-QueueTree')
                    ->with('prefs')
                    ->orderBy(ComponentPref::where('attribute', 'sq-name')
                        ->select('value'))
                    ->get();
            }
            $this->qosPortGraphs = $this->qosDeviceGraphs->filter(
                fn ($comp) => $comp->prefs->filter(
                    fn ($pref) => ($pref->attribute == 'qt-parent' && $pref->value == $parentId)
                )->count()
            );
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.qos_port');
    }
}
