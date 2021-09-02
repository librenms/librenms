<?php

namespace App\View\Components;

use App\Models\Device;
use App\Models\Port;
use Illuminate\View\Component;

class Graph extends Component
{
    const DEFAULT_WIDTH = 340;
    const DEFAULT_HEIGHT = 100;

    public $vars;
    public $width;
    public $height;
    public $type;
    public $from;
    public $to;
    public $legend;
    public $absolute_size;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($type = '', $vars = [], $from = '-1d', $to = null, $legend = 'no', $width = self::DEFAULT_WIDTH, $height = self::DEFAULT_HEIGHT, $absolute_size = 0, $device = null, $port = null)
    {
        $this->type = $type;
        $this->vars = $vars;
        $this->from = $from;
        $this->to = $to;
        $this->legend = $legend;
        $this->width = $width;
        $this->height = $height;
        $this->absolute_size = $absolute_size;

        // handle device and port ids/models for convenience could be set in $vars
        if ($device instanceof Device) {
            $this->vars['device'] = $device->device_id;
        } elseif (is_numeric($device)) {
            $this->vars['device'] = $device;
        } elseif ($port instanceof Port) {
            $this->vars['id'] = $port->port_id;
        } elseif (is_numeric($port)) {
            $this->vars['id'] = $port;
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.graph', [
            'link' => url('graph.php') . '?' . http_build_query($this->vars + [
                        'type' => $this->type,
                        'legend' => $this->legend,
                        'absolute_size' => $this->absolute_size,
                        'width' => $this->width,
                        'height' => $this->height,
                        'from' => $this->from,
                        'to' => $this->to,
                    ])
        ]);
    }
}
