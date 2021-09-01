<?php

namespace App\View\Components;

use App\Models\Port;
use Illuminate\Support\Arr;
use Illuminate\View\Component;
use LibreNMS\Util\Rewrite;
use LibreNMS\Util\Url;

class PortLink extends Component
{
    public $port;
    public $link;
    public $label;
    public $description;
    public $graphs;
    public $vars;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(Port $port, $graphs = ['port_bits'])
    {
        $this->port = $port;
        $this->link = Url::portUrl($port);
        $this->label = Rewrite::normalizeIfName($port->getLabel());
        $this->description = $port->getDescription();
        $this->graphs = Arr::wrap($graphs);
        $this->vars = ['port' => $port->port_id];
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.port-link');
    }

    public function linkClass()
    {
        if ($this->port->ifAdminStatus == 'down') {
            return 'interface-admindown';
        }

        if ($this->port->ifAdminStatus == 'up' && $this->port->ifOperStatus != 'up') {
            return 'interface-updown';
        }

        return 'interface-upup';
    }
}
