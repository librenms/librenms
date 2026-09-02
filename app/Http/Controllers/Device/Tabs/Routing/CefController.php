<?php

/**
 * CefController.php
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
 * @copyright  2026 LibreNMS
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Tabs\Routing;

use App\Http\Controllers\Controller;
use App\Models\CefSwitching;
use App\Models\Device;
use App\Models\EntPhysical;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use LibreNMS\Util\Number;

class CefController extends Controller
{
    public function __invoke(Device $device, Request $request): View
    {
        $this->authorize('view', $device);
        abort_if(Gate::none(['routing.view', 'routing.viewAll']), 403);

        Validator::validate($request->all(), [
            'view' => 'nullable|in:basic,graphs',
        ]);

        $view = $request->query('view', 'basic');

        $cefOptions = [
            'basic' => [
                'text' => __('Basic'),
                'link' => route('device.routing.cef', ['device' => $device, 'view' => 'basic']),
            ],
            'graphs' => [
                'text' => __('Graphs'),
                'link' => route('device.routing.cef', ['device' => $device, 'view' => 'graphs']),
            ],
        ];

        $cefRows = $this->getCefRows($device);

        return view('device.tabs.routing.cef', [
            'device' => $device,
            'view' => $view,
            'cef_options' => $cefOptions,
            'cef_rows' => $cefRows,
        ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function getCefRows(Device $device): Collection
    {
        $cefRows = $device->cefSwitching()
            ->orderBy('entPhysicalIndex')
            ->orderBy('afi')
            ->orderBy('cef_index')
            ->get();

        $entities = $device->entityPhysical()->get()->keyBy('entPhysicalIndex');

        return $cefRows->map(fn (CefSwitching $cef) => $this->formatCefRow($cef, $entities));
    }

    /**
     * @param  Collection<int|string, EntPhysical>  $entities
     * @return array<string, mixed>
     */
    private function formatCefRow(CefSwitching $cef, Collection $entities): array
    {
        $entity = $entities->get($cef->entPhysicalIndex);
        $entityDescr = $this->formatEntityDescr($entity, $cef->entPhysicalIndex, $entities);
        $interval = (int) ($cef->updated - $cef->updated_prev);

        $pathTitle = match ($cef->cef_path) {
            'RP RIB' => __('Process switching with CEF assistance.'),
            'RP LES' => __('Low-end switching. Centralized CEF switch path.'),
            'RP PAS' => __('CEF turbo switch path.'),
            default => null,
        };

        return [
            'id' => $cef->cef_switching_id,
            'entity_descr' => $entityDescr,
            'afi' => $cef->afi,
            'path' => $cef->cef_path,
            'path_title' => $pathTitle,
            'drop' => Number::formatSi($cef->drop, 2, 0, ''),
            'drop_rate' => ($interval > 0 && $cef->drop > $cef->drop_prev) ? round(($cef->drop - $cef->drop_prev) / $interval, 2) : null,
            'punt' => Number::formatSi($cef->punt, 2, 0, ''),
            'punt_rate' => ($interval > 0 && $cef->punt > $cef->punt_prev) ? round(($cef->punt - $cef->punt_prev) / $interval, 2) : null,
            'punt2host' => Number::formatSi($cef->punt2host, 2, 0, ''),
            'punt2host_rate' => ($interval > 0 && $cef->punt2host > $cef->punt2host_prev) ? round(($cef->punt2host - $cef->punt2host_prev) / $interval, 2) : null,
        ];
    }

    /**
     * @param  Collection<int|string, EntPhysical>  $entities
     */
    private function formatEntityDescr(?EntPhysical $entity, mixed $entPhysicalIndex, Collection $entities): string
    {
        if (! $entity) {
            return __('Index') . ' ' . $entPhysicalIndex;
        }

        if (! $entity->entPhysicalModelName && $entity->entPhysicalContainedIn) {
            $parent = $entities->get($entity->entPhysicalContainedIn);

            return $entity->entPhysicalName . ($parent && $parent->entPhysicalModelName ? ' (' . $parent->entPhysicalModelName . ')' : '');
        }

        return $entity->entPhysicalName . ($entity->entPhysicalModelName ? ' (' . $entity->entPhysicalModelName . ')' : '');
    }
}
