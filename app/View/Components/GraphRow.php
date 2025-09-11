<?php

namespace App\View\Components;

use App\Models\Device;
use App\Models\Port;
use Illuminate\View\Component;

class GraphRow extends Component
{
    public bool $responsive;
    public ?int $rowWidth;

    /**
     * Create a new component instance.
     *
     * @param  string  $type
     * @param  string|null  $title
     * @param  string  $loading
     * @param  string  $aspect
     * @param  int|string  $columns  Either a number or 'responsive' aka auto columns
     * @param  array  $graphs
     * @param  Device|int|null  $device
     * @param  Port|int|null  $port
     */
    public function __construct(
        public string $type = '',
        public ?string $title = null,
        public string $loading = 'eager',
        public string $aspect = 'normal',
        public int|string $columns = 2,
        public array $graphs = [['from' => '-1d'], ['from' => '-7d'], ['from' => '-30d'], ['from' => '-1y']],
        public int|Device|null $device = null,
        public int|Port|null $port = null)
    {
        $this->responsive = $columns == 'responsive';
        $this->rowWidth = $this->calculateRowWidth((int) $columns);
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

    private function calculateRowWidth(int $columns): ?int
    {
        if ($this->responsive) {
            return null;
        }

        $max = max(array_column($this->graphs, 'width') + [0]);

        if (! $max) {
            $max = $this->aspect == 'wide' ? Graph::DEFAULT_WIDE_WIDTH : Graph::DEFAULT_NORMAL_WIDTH;
        }

        // width * columns, unless there is less graphs than columns
        return $max * min($columns, count($this->graphs));
    }
}
