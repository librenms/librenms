<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Qos extends Component
{
    public \App\Models\Qos|null $qosGraph = null;
    public string $typePrefix;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public Collection $qosItems,
        public int|null $show = null,
        public int|null $portId = null,
        public int|null $parentId = null,
    ) {
        $this->typePrefix = is_null($portId) ? 'device_qos_' : 'port_qos_';
        if (! is_null($show)) {
            $this->qosGraph = $qosItems->where('qos_id', $show)->first();
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.qos');
    }
}
