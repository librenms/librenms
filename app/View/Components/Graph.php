<?php

namespace App\View\Components;

use App\Models\Device;
use App\Models\Port;
use Illuminate\Support\Arr;
use Illuminate\View\Component;
use Illuminate\View\View;

class Graph extends Component
{
    const DEFAULT_WIDE_WIDTH = 340;
    const DEFAULT_WIDE_ASPECT_RATIO = 3.4;
    const DEFAULT_NORMAL_WIDTH = 300;
    const DEFAULT_NORMAL_ASPECT_RATIO = 2.0;

    /** @var array<string, int> */
    const BREAKPOINTS = [
        '2xl' => 1536,
        'xl' => 1280,
        'lg' => 1024,
        'md' => 768,
        'sm' => 640,
    ];

    public ?int $width;
    public ?int $height;
    private readonly bool $popup;

    public function __construct(
        public string $type = '',
        public array $vars = [],
        public int|string $from = '-1d',
        public int|string|null $to = null,
        public string $legend = 'no',
        string $aspect = 'normal',
        ?int $width = null,
        ?int $height = null,
        public array $columns = [],
        public int $absolute_size = 0,
        private readonly bool|string $link = true,
        bool|string $popup = false,
        public mixed $popupTitle = '',
        Device|int|null $device = null,
        Port|int|null $port = null
    ) {
        $isWide = $aspect === 'wide';
        $this->width = $width ?: $this->resolveDefaultWidth($isWide);
        $this->height = $height ?: $this->calculateDefaultHeight($this->width, $isWide);
        $this->popup = filter_var($popup, FILTER_VALIDATE_BOOLEAN);
        $this->vars = $this->resolveVars($this->vars, $device, $port);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        $view = $this->popup ? 'components.graph-popup' : ($this->link === false ? 'components.graph' : 'components.linked-graph');

        return view($view, [
            'link' => $this->getLink(),
            'src' => $this->getSrc(),
        ]);
    }

    public function filterAttributes(mixed $value, int|string $key): bool
    {
        $filtered = ['legend', 'height', 'loading', 'img-class'];

        // do not add class and style to the image, add them to the outer link
        if ($this->link) {
            $filtered[] = 'class';
            $filtered[] = 'style';
        }

        return ! in_array($key, $filtered);
    }

    /**
     * Fold a device or port reference into the graph vars, preferring device over port.
     *
     * @return array<string, mixed>
     */
    private function resolveVars(array $vars, Device|int|null $device, Port|int|null $port): array
    {
        if ($device instanceof Device) {
            return [...$vars, 'device' => $device->device_id];
        }

        if (is_numeric($device)) {
            return [...$vars, 'device' => $device];
        }

        if ($port instanceof Port) {
            return [...$vars, 'id' => $port->port_id];
        }

        if (is_numeric($port)) {
            return [...$vars, 'id' => $port];
        }

        return $vars;
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
     * Base width for this aspect, divided across configured columns when the graph
     * is in a column grid and the screen width is known.
     */
    private function resolveDefaultWidth(bool $isWide): int
    {
        $screenWidth = session('screen_width');

        if ($this->columns === [] || ! is_numeric($screenWidth)) {
            return $isWide ? self::DEFAULT_WIDE_WIDTH : self::DEFAULT_NORMAL_WIDTH;
        }

        return intdiv((int) $screenWidth, $this->columnsFor((int) $screenWidth));
    }

    /**
     * Use the default aspect ratio to calculate the graph height based on the resolved width.
     */
    private function calculateDefaultHeight(int $width, bool $isWide): int
    {
        $aspectRatio = $isWide ? self::DEFAULT_WIDE_ASPECT_RATIO : self::DEFAULT_NORMAL_ASPECT_RATIO;

        return (int) round($width / $aspectRatio);
    }

    /**
     * Column count for the largest configured breakpoint the screen width satisfies.
     */
    private function columnsFor(int $screenWidth): int
    {
        foreach (self::BREAKPOINTS as $breakpoint => $minimumWidth) {
            if ($screenWidth >= $minimumWidth && array_key_exists($breakpoint, $this->columns)) {
                return max(1, $this->columns[$breakpoint]);
            }
        }

        return 1;
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
