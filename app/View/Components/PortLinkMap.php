<?php

namespace App\View\Components;

use App\Models\Port;
use Illuminate\Support\Arr;
use Illuminate\View\Component;
use LibreNMS\Util\Rewrite;
use LibreNMS\Util\Url;

class PortLinkMap extends Component
{
    /**
     * @var \App\Models\Port
     */
    public $port;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(Port $port)
    {
        $this->port = $port;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.port-link-map', [
            'graphs' => [['type' => 'port_bits', 'title' => trans('Traffic')]],
        ]);
    }
}
