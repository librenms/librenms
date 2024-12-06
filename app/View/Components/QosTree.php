<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class QosTree extends Component
{
    public Collection $listItems;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public Collection $qosItems,
        public int|null $parentPortId = null,
        public int|null $parentQosId = null,
        public int|null $show = null,
    ) {
        if (! is_null($parentQosId)) {
            $this->listItems = $qosItems->where('parent_id', $parentQosId)->sortBy('title');
        } elseif (! is_null($parentPortId)) {
            $this->listItems = $qosItems->where('port_id', $parentPortId)->sortBy('title');
        } else {
            $this->listItems = $qosItems->whereNull('parent_id')->sortBy('title');
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.qos-tree');
    }
}
