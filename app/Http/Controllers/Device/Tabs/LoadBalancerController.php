<?php

/**
 * LoadBalancerController.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Tabs;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Device\Tabs\LoadBalancer\AcosSource;
use App\Http\Controllers\Device\Tabs\LoadBalancer\CiscoAceSource;
use App\Http\Controllers\Device\Tabs\LoadBalancer\Data\LoadBalancerItem;
use App\Http\Controllers\Device\Tabs\LoadBalancer\F5Source;
use App\Http\Controllers\Device\Tabs\LoadBalancer\LoadBalancerSection;
use App\Http\Controllers\Device\Tabs\LoadBalancer\LoadBalancerSource;
use App\Http\Controllers\Device\Tabs\LoadBalancer\NetscalerSource;
use App\Models\Device;
use Closure;
use Illuminate\Container\Attributes\Scoped;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use LibreNMS\Interfaces\UI\DeviceTab;
use WeakMap;

/**
 * Scoped so the tab bar and the page share the sources and their counts for the same Device
 */
#[Scoped]
class LoadBalancerController extends Controller implements DeviceTab
{
    /** @var WeakMap<Device, LoadBalancerSource|null> */
    private WeakMap $sources;

    public function __construct()
    {
        $this->sources = new WeakMap;
    }

    public function visible(Device $device): bool
    {
        return ! empty($this->source($device)?->counts());
    }

    public function slug(): string
    {
        return 'loadbalancer';
    }

    public function icon(): string
    {
        return 'fa-balance-scale';
    }

    public function name(): string
    {
        return __('Load Balancer');
    }

    /**
     * @return array<string, mixed>
     */
    public function data(Device $device, Request $request): array
    {
        return [];
    }

    public function index(Device $device, Request $request): View
    {
        $this->authorize('view', $device);

        $link = fn (LoadBalancerSection $section, array $params = []): string => $this->link($device, $section, $params);
        $source = $this->source($device);
        $counts = $source?->counts() ?? [];
        $sections = array_filter(LoadBalancerSection::cases(), fn (LoadBalancerSection $section) => isset($counts[$section->value]));
        $request->validate([
            'type' => 'nullable|string',
            'id' => 'nullable|integer',
            'graph' => 'nullable|string',
            'sort' => 'nullable|string',
            'order' => 'nullable|in:asc,desc',
        ]);

        $section = $request->enum('type', LoadBalancerSection::class);
        $section = in_array($section, $sections, true) ? $section : reset($sections);

        $data = [
            'device' => $device,
            'section' => $section ? $section->value : null,
            'options' => collect($sections)->mapWithKeys(fn (LoadBalancerSection $option) => [$option->value => [
                'text' => $option->title() . ' (' . $counts[$option->value] . ')',
                'link' => $link($option),
            ]])->all(),
            'link' => $link,
            'graphs' => [],
            'graphVars' => [],
        ];

        if ($source === null || ! $section) {
            return view('device.tabs.loadbalancer', $data);
        }

        $items = $source->items($section);
        $item = $request->filled('id') ? $items->get($request->integer('id')) : null;

        if ($item) {
            $members = $source->members($section, $item);

            return view('device.tabs.loadbalancer', [
                'page' => 'detail',
                'item' => $item,
                'columns' => $source->columns($section),
                'members' => $members->isEmpty() ? null : $this->table($device, $members, LoadBalancerSection::PoolMembers, $source->columns(LoadBalancerSection::PoolMembers), showPool: false),
                'graphs' => $source->itemGraphs($section),
                'graphVars' => ['id' => $item->id],
            ] + $data);
        }

        // the list can show one of the item graphs under each row
        $itemGraphs = $source->itemGraphs($section);
        $rowGraph = $request->string('graph')->value();
        $rowGraph = isset($itemGraphs[$rowGraph]) ? $rowGraph : null;
        $sorting = [
            'sort' => $request->string('sort')->value() ?: 'name',
            'order' => $request->string('order')->value() ?: 'asc',
            'graph' => $rowGraph,
        ];

        return view('device.tabs.loadbalancer', [
            'page' => 'list',
            'table' => $this->table($device, $items, $section, $source->columns($section), $sorting),
            'rowGraph' => $rowGraph,
            'rowGraphOptions' => empty($itemGraphs) ? [] : collect(['' => 'None'] + $itemGraphs)->map(fn (string $title, string $graph) => [
                'text' => __($title),
                'link' => $link($section, ['graph' => $graph, 'sort' => $sorting['sort'], 'order' => $sorting['order']]),
            ])->all(),
            'graphs' => $source->sectionGraphs($section),
        ] + $data);
    }

    private function source(Device $device): ?LoadBalancerSource
    {
        if (! $this->sources->offsetExists($device)) {
            $this->sources[$device] = match ($device->os) {
                'acos' => new AcosSource($device),
                'f5' => new F5Source($device),
                'netscaler' => new NetscalerSource($device),
                'acsw' => new CiscoAceSource($device),
                default => null,
            };
        }

        return $this->sources[$device];
    }

    /**
     * @param  array<string, mixed>  $params
     */
    private function link(Device $device, LoadBalancerSection $section, array $params = []): string
    {
        return route('device.loadbalancer', ['device' => $device->device_id, 'type' => $section->value] + array_filter($params));
    }

    /**
     * What the item table shows, columns without values left out.
     * With $sorting, the items are sorted and the headers link to sorting by their column.
     *
     * @param  Collection<int, LoadBalancerItem>  $items
     * @param  array<string, string>  $columnTitles  the source's columns, column key => title
     * @param  array{sort: string, order: string, graph: string|null}|null  $sorting
     * @return array{items: Collection<int, LoadBalancerItem>, section: LoadBalancerSection, address: bool, pool: bool, columns: array<string, string>, headers: list<array{title: string, link: string|null, order: string|null}>}
     */
    private function table(Device $device, Collection $items, LoadBalancerSection $section, array $columnTitles, ?array $sorting = null, bool $showPool = true): array
    {
        $address = $items->contains(fn (LoadBalancerItem $item) => $item->address !== null);
        $pool = $showPool && $items->contains(fn (LoadBalancerItem $item) => $item->pool !== null);
        $columns = array_filter($columnTitles, fn (string $key) => $items->contains(fn (LoadBalancerItem $item) => isset($item->columns[$key])), ARRAY_FILTER_USE_KEY);

        /** @var array<string, array{string, Closure(LoadBalancerItem): mixed}> $fields sort key => [title, the value to sort an item by] */
        $fields = ['name' => [__('Name'), fn (LoadBalancerItem $item) => $item->name]];
        if ($address) {
            $fields['address'] = [__('Address'), fn (LoadBalancerItem $item) => $item->address];
        }
        if ($pool) {
            $fields['pool'] = [__('Pool'), fn (LoadBalancerItem $item) => $item->pool];
        }
        foreach ($columns as $key => $title) {
            $fields[$key] = [$title, fn (LoadBalancerItem $item) => $item->sortValues[$key] ?? $item->columns[$key] ?? null];
        }
        $fields['status'] = [__('Status'), fn (LoadBalancerItem $item) => $item->severity->value];

        $sort = $sorting && isset($fields[$sorting['sort']]) ? $sorting['sort'] : null;
        if ($sort) {
            $value = $fields[$sort][1];
            $direction = $sorting['order'] == 'desc' ? -1 : 1;
            $items = $items->sort(fn (LoadBalancerItem $a, LoadBalancerItem $b) => $direction * $this->compare($value($a), $value($b)));
        }

        return [
            'items' => $items,
            'section' => $section,
            'address' => $address,
            'pool' => $pool,
            'columns' => $columns,
            'headers' => collect($fields)->map(fn (array $field, string $key) => [
                'title' => $field[0],
                'link' => $sorting ? $this->link($device, $section, [
                    'graph' => $sorting['graph'],
                    'sort' => $key,
                    'order' => $key == $sort && $sorting['order'] == 'asc' ? 'desc' : 'asc',
                ]) : null,
                'order' => $key == $sort ? $sorting['order'] : null,
            ])->values()->all(),
        ];
    }

    /**
     * Numbers compare by value, everything else in natural order ignoring case
     */
    private function compare(mixed $a, mixed $b): int
    {
        if (is_numeric($a) && is_numeric($b)) {
            return $a <=> $b;
        }

        return strnatcasecmp((string) $a, (string) $b);
    }
}
