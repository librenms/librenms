<?php

namespace App\View\Components;

use App\Models\Device;
use App\Models\Port;
use Illuminate\View\Component;

class Graph extends Component
{
    const DEFAULT_WIDE_WIDTH = 340;
    const DEFAULT_WIDE_HEIGHT = 100;
    const DEFAULT_NORMAL_WIDTH = 300;
    const DEFAULT_NORMAL_HEIGHT = 150;

    /**
     * @var array
     */
    public $vars;
    /**
     * @var int|null
     */
    public $width;
    /**
     * @var int|null
     */
    public $height;
    /**
     * @var string
     */
    public $type;
    /**
     * @var int|string|null
     */
    public $from;
    /**
     * @var int|string|null
     */
    public $to;
    /**
     * @var string
     */
    public $legend;
    /**
     * @var int
     */
    public $absolute_size;
    /**
     * @var bool
     */
    private $link;
    /**
     * @var bool|string
     */
    private $popup;
    /**
     * @var string
     */
    public mixed $popupTitle;

    /**
     * Create a new component instance.
     *
     * @param  string  $type
     * @param  array  $vars
     * @param  int|string  $from
     * @param  int|string  $to
     * @param  string  $legend
     * @param  string  $aspect
     * @param  int|null  $width
     * @param  int|null  $height
     * @param  int  $absolute_size
     * @param  \App\Models\Device|int|null  $device
     * @param  \App\Models\Port|int|null  $port
     */
    public function __construct(
        string $type = '',
        array $vars = [],
        $from = '-1d',
        $to = null,
        string $legend = 'no',
        string $aspect = 'normal',
        ?int $width = null,
        ?int $height = null,
        int $absolute_size = 0,
        $link = true,
        $popup = false,
        $popupTitle = '',
        $device = null,
        $port = null
    ) {
        $this->type = $type;
        $this->vars = $vars;
        $this->from = $from;
        $this->to = $to;
        $this->legend = $legend;
        $this->absolute_size = $absolute_size;
        $this->width = $width ?: ($aspect == 'wide' ? self::DEFAULT_WIDE_WIDTH : self::DEFAULT_NORMAL_WIDTH);
        $this->height = $height ?: ($aspect == 'wide' ? self::DEFAULT_WIDE_HEIGHT : self::DEFAULT_NORMAL_HEIGHT);
        $this->popupTitle = $popupTitle;
        $this->popup = filter_var($popup, FILTER_VALIDATE_BOOLEAN);
        $this->link = $link;

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
        $view = $this->popup ? 'components.graph-popup' : ($this->link === false ? 'components.graph' : 'components.linked-graph');
        $data = [
            'link' => $this->getLink(),
            'src' => $this->getSrc(),
        ];

        return view($view, $data);
    }

    /**
     * @param  mixed  $value
     * @param  int|string  $key
     * @return bool
     */
    public function filterAttributes($value, $key): bool
    {
        $filtered = [
            'legend',
            'height',
            'loading',
        ];

        // do not add class and style to the image, add them to the outer link
        if ($this->link) {
            $filtered[] = 'class';
            $filtered[] = 'style';
        }

        return ! in_array($key, $filtered);
    }

    private function getSrc(): string
    {
        return url('graph.php') . '?' . http_build_query($this->vars + [
            'type' => $this->type,
            'legend' => $this->legend,
            'absolute_size' => $this->absolute_size,
            'width' => $this->width,
            'height' => $this->height,
            'from' => $this->from,
            'to' => $this->to,
        ]);
    }

    private function getLink(): string
    {
        return match ($this->link) {
            true => url('graphs') . '/' . http_build_query($this->vars + [
                'type' => $this->type,
                'from' => $this->from,
                'to' => $this->to,
            ], '', '/'),
            false => '',
            default => $this->link,
        };
    }
}
