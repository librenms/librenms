<?php

namespace App\View\Components;

use Illuminate\View\Component;

class GraphRow extends Component
{
    /**
     * @var string
     */
    public $type;
    /**
     * @var string
     */
    public $loading;
    /**
     * @var null
     */
    public $device;
    /**
     * @var null
     */
    public $port;
    /**
     * @var array|\string[][]
     */
    public $graphs;
    /**
     * @var string|null
     */
    public $title;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(string $type = '', string $title = null, string $loading = 'eager', $device = null, $port = null, array $graphs = [['from' => '-1d'], ['from' => '-7d'], ['from' => '-30d'], ['from' => '-1y']])
    {
        $this->type = $type;
        $this->loading = $loading;
        $this->device = $device;
        $this->port = $port;
        $this->graphs = $graphs;
        $this->title = $title;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.graph-row');
    }
}
