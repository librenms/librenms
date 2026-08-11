<?php

namespace App\View\Components;

use App\Models\Device;
use App\Models\Port;
use Illuminate\Support\Arr;
use Illuminate\View\Component;

class Graph extends Component
{
    const DEFAULT_WIDE_WIDTH = 340;
    const DEFAULT_WIDE_ASPECT_RATIO = 3.4;
    const DEFAULT_NORMAL_WIDTH = 300;
    const DEFAULT_NORMAL_ASPECT_RATIO = 2.0;

    /** @var array<string, int> */
    const BREAKPOINTS = [
        'sm' => 640,
        'md' => 768,
        'lg' => 1024,
        'xl' => 1280,
        '2xl' => 1536,
    ];

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
    private $popup;

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
     * @param  array<string, int>  $columns
     * @param  int  $absolute_size
     * @param  Device|int|null  $device
     * @param  Port|int|null  $port
     * @param  bool  $link
     * @param  string  $popupTitle
     */
    public function __construct(
        string $type = '',
        array $vars = [],
        public $from = '-1d',
        public $to = null,
        string $legend = 'no',
        string $aspect = 'normal',
        ?int $width = null,
        ?int $height = null,
        public array $columns = [],
        int $absolute_size = 0,
        private $link = true,
        $popup = false,
        public mixed $popupTitle = '',
        $device = null,
        $port = null
    ) {
        $this->type = $type;
        $this->vars = $vars;
        $this->legend = $legend;
        $this->absolute_size = $absolute_size;
        [$this->width, $this->height] = $this->graphDimensions($aspect, $width, $height);
        $this->popup = filter_var($popup, FILTER_VALIDATE_BOOLEAN);

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
            'img-class',
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
        return route('graph', [
            'type' => $this->type,
            'legend' => $this->legend,
            'absolute_size' => $this->absolute_size,
            'width' => $this->width,
            'height' => $this->height,
            'from' => $this->from,
            'to' => $this->to,
            ...$this->vars,
        ]);
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function graphDimensions(string $aspect, ?int $width, ?int $height): array
    {
        [$defaultWidth, $aspectRatio] = $this->defaultGraphDimensions($aspect);

        $width = $width ?: $defaultWidth;
        $height = $height ?: (int) round($width / $aspectRatio);

        return [$width, $height];
    }

    /**
     * @return array{0: int, 1: float}
     */
    private function defaultGraphDimensions(string $aspect): array
    {
        [$defaultWidth, $aspectRatio] = $aspect === 'wide'
            ? [self::DEFAULT_WIDE_WIDTH, self::DEFAULT_WIDE_ASPECT_RATIO]
            : [self::DEFAULT_NORMAL_WIDTH, self::DEFAULT_NORMAL_ASPECT_RATIO];

        if ($aspect === 'wide' || $this->columns !== []) {
            $defaultWidth = $this->responsiveGraphWidth($defaultWidth);
        }

        return [$defaultWidth, $aspectRatio];
    }

    private function responsiveGraphWidth(int $defaultWidth): int
    {
        $screenWidth = session('screen_width');
        if (! is_numeric($screenWidth)) {
            return $defaultWidth;
        }

        $screenWidth = (int) $screenWidth;

        return intdiv($screenWidth, $this->columnsFor($screenWidth));
    }

    private function columnsFor(int $screenWidth): int
    {
        $columns = 1;
        foreach (self::BREAKPOINTS as $breakpoint => $minimumWidth) {
            if ($screenWidth >= $minimumWidth && array_key_exists($breakpoint, $this->columns)) {
                $columns = max(1, $this->columns[$breakpoint]);
            }
        }

        return $columns;
    }

    private function getLink(): string
    {
        return match ($this->link) {
            true => route('graphs', [
                'type' => $this->type,
                'from' => $this->from,
                'to' => $this->to,
                ...Arr::except($this->vars, ['width', 'height']),
            ]),
            false => '',
            default => $this->link,
        };
    }
}
