<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class QosTree extends Component
{
    /** @var Collection<int, \App\Models\Qos> */
    public Collection $listItems;

    /**
     * Create a new component instance.
     *
     * @param  Collection<int, \App\Models\Qos>  $qosItems
     */
    public function __construct(
        public Collection $qosItems,
        public int $deviceId,
        public ?int $portId = null,
        public ?int $parentQosId = null,
        public ?int $show = null,
    ) {
        if (! is_null($parentQosId)) {
            $this->listItems = $qosItems->where('parent_id', $parentQosId)->sortBy('title');
        } elseif (! is_null($portId)) {
            $this->listItems = $qosItems->where('port_id', $portId)->sortBy('title');
        } else {
            $this->listItems = $qosItems->whereNull('parent_id')->sortBy('title');
        }
    }

    /**
     * Link that selects a QoS policy on the port or device QoS page
     */
    public function link(int $qosId): string
    {
        if ($this->portId) {
            return route('device.port', ['device' => $this->deviceId, 'port' => $this->portId, 'view' => 'qos', 'show' => $qosId]);
        }

        return route('device.qos', ['device' => $this->deviceId, 'show' => $qosId]);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.qos-tree');
    }
}
