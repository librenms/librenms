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
     * @var array|string[][]
     */
    public $graphs;
    /**
     * @var string|null
     */
    public $title;
    /**
     * @var float|int
     */
    public $rowWidth;
    /**
     * @var bool
     */
    public $responsive;
    /**
     * @var string
     */
    public $aspect;

    /**
     * Create a new component instance.
     *
     * @param  string  $type
     * @param  string|null  $title
     * @param  string  $loading
     * @param  string  $aspect
     * @param  int|string  $columns  Either a number or 'responsive' aka auto columns
     * @param  array  $graphs
     * @param  \App\Models\Device|int|null  $device
     * @param  \App\Models\Port|int|null  $port
     */
    public function __construct(string $type = '', string $title = null, string $loading = 'eager', string $aspect = 'normal', $columns = 2, array $graphs = [['from' => '-1d'], ['from' => '-7d'], ['from' => '-30d'], ['from' => '-1y']], $device = null, $port = null)
    {
        $this->type = $type;
        $this->aspect = $aspect;
        $this->loading = $loading;
        $this->device = $device;
        $this->port = $port;
        $this->graphs = $graphs;
        $this->title = $title;
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
