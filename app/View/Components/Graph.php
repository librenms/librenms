<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Graph extends Component
{
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
    public function __construct($type = '', $vars = [], $start = null, $end = null, $legend = 'no', $width = 340, $height = 100, $class = 'graph-image', $loading = 'eager', $absolute_size = 0, $trim = 0)
    {
        $this->link = url('graph.php') . '?' . http_build_query($vars + [
            'type' => $type,
            'legend' => $legend,
            'trim' => $trim,
            'absolute_size' => $absolute_size,
            'width' => $width,
            'height' => $height,
            'from' => $start,
            'to' => $end,
            ]);

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
        return view('components.graph');
    }
}
