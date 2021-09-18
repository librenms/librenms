<?php

namespace App\View\Components;

use App\Models\Port;
use Illuminate\Support\Arr;
use Illuminate\View\Component;
use LibreNMS\Util\Rewrite;
use LibreNMS\Util\Url;

class PortLink extends Component
{
    /**
     * @var \App\Models\Port
     */
    public $port;
    /**
     * @var string
     */
    public $link;
    /**
     * @var array|string|string[]
     */
    public $label;
    /**
     * @var string
     */
    public $description;
    /**
     * @var array|array[]
     */
    public $graphs;
    /**
     * @var string
     */
    public $status;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(Port $port, ?array $graphs = null)
    {
        $this->port = $port;
        $this->link = Url::portUrl($port);
        $this->label = Rewrite::normalizeIfName($port->getLabel());
        $this->description = $port->getDescription();
        $this->status = $this->status();

        $this->graphs = $graphs === null ? [
            ['type' => 'port_bits', 'title' => trans('Traffic'), 'vars' => [['from' => '-1d'], ['from' => '-7d'], ['from' => '-30d'], ['from' => '-1y']]],
        ] : Arr::wrap($graphs);

        if ($this->description == $this->label) {
            $this->description = '';
        }
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

    private function status(): string
    {
        if ($this->port->ifAdminStatus == 'down') {
            return 'disabled';
        }

        return $this->port->ifAdminStatus == 'up' && $this->port->ifOperStatus != 'up'
            ? 'down'
            : 'up';
    }

    public function fillDefaultVars(array $vars): array
    {
        return array_map(function ($graph_vars) {
            return array_merge([
                'from' => '-1d',
                'type' => 'port_bits',
                'legend' => 'yes',
                'text' => '',
            ], Arr::wrap($graph_vars));
        }, $vars);
    }
}
