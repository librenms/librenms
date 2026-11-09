<?php

namespace App\View\Components;

use App\Facades\Rrd;
use App\Models\Vminfo;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Illuminate\View\Component;
use LibreNMS\Util\Url;

class VminfoGraphs extends Component
{
    public const PER_PAGE_OPTIONS = [48, 96, 240];

    /** @var list<array{link: string, graphTag: string}> */
    public array $tiles;

    /**
     * @param  LengthAwarePaginator<int, Vminfo>  $vms
     * @param  array<int, array<string, mixed>>  $filterFields
     * @param  array<string, mixed>  $filter
     */
    public function __construct(
        public LengthAwarePaginator $vms,
        public string $graph,
        public array $filterFields = [],
        public array $filter = [],
        public ?string $from = null,
        public ?string $to = null,
    ) {
        $graphTemplate = array_filter([
            'type' => self::types()[$graph]['type'],
            'from' => $from ?? '-1d',
            'to' => $to,
            'height' => 110,
            'width' => session('widescreen') ? 270 : 315,
            'legend' => 'no',
            'title' => 'yes',
        ]);

        $metric = self::types()[$graph]['metric'];

        // not every source records metrics
        $this->tiles = $vms->getCollection()
            ->filter(fn (Vminfo $vm): bool => Rrd::checkRrdExists(Rrd::metricName($vm->metricIdentity($metric))))
            ->map(fn (Vminfo $vm): array => [
                'link' => route('device.vm.show', ['device' => $vm->device_id, 'vminfo' => $vm->id]),
                'graphTag' => Url::lazyGraphTag(['id' => $vm->id] + $graphTemplate, 'tw:w-full tw:h-auto'),
            ])->values()->all();
    }

    /**
     * Graphs a virtual machine may record, keyed by the slug used in urls.
     *
     * @return array<string, array{type: string, metric: string, title: string, label: string}>
     */
    public static function types(): array
    {
        return [
            'cpu' => ['type' => 'vminfo_cpu', 'metric' => 'vm.cpu', 'title' => __('CPU Usage'), 'label' => __('CPU')],
            'mem' => ['type' => 'vminfo_mem', 'metric' => 'vm.memory', 'title' => __('Memory Usage'), 'label' => __('Memory')],
            'disk' => ['type' => 'vminfo_disk', 'metric' => 'vm.disk', 'title' => __('Disk Usage'), 'label' => __('Disk')],
            'diskio_bits' => ['type' => 'vminfo_diskio_bits', 'metric' => 'vm.diskio', 'title' => __('Disk I/O'), 'label' => __('Disk I/O')],
            'diskio_ops' => ['type' => 'vminfo_diskio_ops', 'metric' => 'vm.diskops', 'title' => __('Disk Operations'), 'label' => __('Disk Ops')],
            'net' => ['type' => 'vminfo_net', 'metric' => 'vm.net', 'title' => __('Network Traffic'), 'label' => __('Network')],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function validationRules(): array
    {
        return [
            'graph' => ['nullable', Rule::in(array_keys(self::types()))],
            'per_page' => ['nullable', Rule::in(self::PER_PAGE_OPTIONS)],
            'from' => ['nullable', 'date_or_relative'],
            'to' => ['nullable', 'date_or_relative'],
        ];
    }

    /**
     * Option bar entries to switch between the list and each graph.
     *
     * @param  array<string, mixed>  $query  current query string
     * @param  Closure(array<string, mixed>): string  $url  builds a link to the page from a query string
     * @return array<string, array{text: string, link: string}>
     */
    public static function nav(array $query, Closure $url): array
    {
        $query = array_diff_key($query, array_flip(['graph', 'page']));
        $nav = ['list' => ['text' => __('List'), 'link' => $url(array_diff_key($query, array_flip(['per_page', 'from', 'to'])))]];

        foreach (self::types() as $slug => $graph) {
            $nav[$slug] = ['text' => $graph['label'], 'link' => $url(['graph' => $slug] + $query)];
        }

        return $nav;
    }

    public function render(): View|Closure|string
    {
        return view('components.vminfo-graphs');
    }
}
