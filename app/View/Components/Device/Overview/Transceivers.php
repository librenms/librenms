<?php

namespace App\View\Components\Device\Overview;

use App\Models\Device;
use App\Models\Sensor;
use App\Models\Transceiver;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Transceivers extends Component
{
    /** @var Collection<int, Sensor> */
    public Collection $transceiverSensors;

    public function __construct(public Device $device)
    {
        $this->transceiverSensors = $device->sensors->where('group', 'transceiver');
    }

    public function shouldShow(Sensor $sensor, Transceiver $transceiver): bool
    {
        if ($sensor->entPhysicalIndex === null || $sensor->entPhysicalIndex != $transceiver->entity_physical_index) {
            return false;
        }

        if ($sensor->sensor_class == 'temperature') {
            return true;
        }

        if ($sensor->sensor_class != 'dbm') {
            return false;
        }

        $description = strtolower((string) $sensor->sensor_descr);

        return str_contains($description, 'rx') || str_contains($description, 'receive');
    }

    public function render(): View|Closure|string
    {
        return view('components.device.overview.transceivers');
    }
}
