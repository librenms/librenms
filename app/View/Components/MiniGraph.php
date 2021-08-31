<?php

namespace App\View\Components;

use Illuminate\View\Component;

class MiniGraph extends Component
{
    public $device;
    public $class;
    public $width;
    public $height;
    public $link;
    public $loading;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($device, $type, $start = null, $end = null, $legend = 'no', $width = 275, $height = 100, $class = 'minigraph-image', $loading = 'eager', $absolute_size = 0)
    {
        $vars = ['device=' . $device->device_id, "from=$start", "to=$end", "width=$width", "height=$height", "type=$type", "legend=$legend", "absolute=$absolute_size"];
        $this->link = url('graph.php') . '?' . implode('&', $vars);

        $this->device = $device;
        $this->width = $width;
        $this->height = $height;
        $this->class = $class;
        $this->loading = $loading;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.mini-graph');
    }
}
