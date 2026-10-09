<?php

namespace App\View\Components;

use App\Models\Sensor;
use App\Models\Transceiver;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class TransceiverSensors extends Component
{
    /** @var Collection<int, \App\Models\Sensor> */
    public Collection $sensors;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public Transceiver $transceiver,
    ) {
        $this->sensors = Sensor::where('device_id', $this->transceiver->device_id)
            ->whereNotNull('entPhysicalIndex')
            ->where('entPhysicalIndex', $this->transceiver->entity_physical_index)
            ->where('group', 'transceiver')
            ->orderBy('sensor_class')
            ->orderBy('sensor_id')
            ->get();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.transceiver-sensors');
    }
}
