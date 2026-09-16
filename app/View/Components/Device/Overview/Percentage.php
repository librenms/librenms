<?php

namespace App\View\Components\Device\Overview;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use LibreNMS\Util\Color;

class Percentage extends Component
{
    public float $value;
    /**
     * @var string[]
     */
    public array $colors;
    public ?float $shadowWidth;
    public ?string $graphUrl;

    /**
     * @param  array{left: string, middle?: string, right: string}|null  $colors
     * @param  array<string, int|string>  $graphVars
     */
    public function __construct(
        float|int $percent,
        public float|int|null $warning = null,
        public ?string $leftText = null,
        public ?string $rightText = null,
        public float|int|null $shadow = null,
        ?array $colors = null,
        public ?string $graphType = null,
        public array $graphVars = [],
        public ?string $graphTitle = null,
        public string $graphFrom = '-1d',
    ) {
        $this->value = max(0, min(100, (float) $percent));
        $this->colors = $colors ?? Color::percentage($this->value, $warning, '#');
        $this->shadowWidth = $shadow === null ? null : max(0, min(100, (float) $shadow - $this->value));
        $this->graphUrl = $graphType
            ? route('graphs', ['type' => $graphType, 'from' => $this->graphFrom, ...$graphVars])
            : null;
    }

    public function render(): View|Closure|string
    {
        return view('components.device.overview.percentage');
    }
}
