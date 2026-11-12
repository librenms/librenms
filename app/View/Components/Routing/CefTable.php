<?php

/**
 * CefTable.php
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 LibreNMS
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\View\Components\Routing;

use App\Models\CefSwitching;
use App\Models\EntPhysical;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use LibreNMS\Util\Number;

/**
 * @phpstan-type CefRow array{
 *     cef: CefSwitching,
 *     entity_descr: string,
 *     path_title: string|null,
 *     drop: string,
 *     drop_rate: float|null,
 *     punt: string,
 *     punt_rate: float|null,
 *     punt2host: string,
 *     punt2host_rate: float|null,
 * }
 */
class CefTable extends Component
{
    /** @var list<CefRow> */
    public array $rows;

    /**
     * @param  EloquentCollection<int, CefSwitching>  $cefs
     */
    public function __construct(
        EloquentCollection $cefs,
        public readonly bool $graphs = false,
        public readonly bool $showDevice = false,
    ) {
        if ($showDevice) {
            $cefs->loadMissing('device');
        }

        $entities = EntPhysical::query()
            ->whereIn('device_id', $cefs->pluck('device_id')->unique())
            ->get(['device_id', 'entPhysicalIndex', 'entPhysicalName', 'entPhysicalModelName', 'entPhysicalContainedIn'])
            ->keyBy(fn (EntPhysical $entity) => "$entity->device_id|$entity->entPhysicalIndex");

        $this->rows = $cefs->map(fn (CefSwitching $cef) => $this->formatRow($cef, $entities))->values()->all();
    }

    public function render(): View|Closure|string
    {
        return view('components.routing.cef-table');
    }

    /**
     * @param  Collection<string, EntPhysical>  $entities
     * @return CefRow
     */
    private function formatRow(CefSwitching $cef, Collection $entities): array
    {
        $interval = (int) ($cef->updated - $cef->updated_prev);

        return [
            'cef' => $cef,
            'entity_descr' => $this->formatEntityDescr($cef, $entities),
            'path_title' => match ($cef->cef_path) {
                'RP RIB' => __('Process switching with CEF assistance.'),
                'RP LES' => __('Low-end switching. Centralized CEF switch path.'),
                'RP PAS' => __('CEF turbo switch path.'),
                default => null,
            },
            'drop' => Number::formatSi($cef->drop, 2, 0, ''),
            'drop_rate' => $this->rate($cef->drop, $cef->drop_prev, $interval),
            'punt' => Number::formatSi($cef->punt, 2, 0, ''),
            'punt_rate' => $this->rate($cef->punt, $cef->punt_prev, $interval),
            'punt2host' => Number::formatSi($cef->punt2host, 2, 0, ''),
            'punt2host_rate' => $this->rate($cef->punt2host, $cef->punt2host_prev, $interval),
        ];
    }

    private function rate(int|float|null $value, int|float|null $previous, int $interval): ?float
    {
        return ($interval > 0 && $value > $previous) ? round(($value - $previous) / $interval, 2) : null;
    }

    /**
     * @param  Collection<string, EntPhysical>  $entities
     */
    private function formatEntityDescr(CefSwitching $cef, Collection $entities): string
    {
        $entity = $entities->get("$cef->device_id|$cef->entPhysicalIndex");

        if (! $entity) {
            return __('Index') . ' ' . $cef->entPhysicalIndex;
        }

        $model = $entity->entPhysicalModelName;
        if (! $model && $entity->entPhysicalContainedIn) {
            $model = $entities->get("$cef->device_id|$entity->entPhysicalContainedIn")?->entPhysicalModelName;
        }

        return $entity->entPhysicalName . ($model ? " ($model)" : '');
    }
}
