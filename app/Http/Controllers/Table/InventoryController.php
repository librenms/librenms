<?php

/**
 * InventoryController.php
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
 * @copyright  2023 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Table;

use App\Models\EntPhysical;
use App\Models\Transceiver;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Util\StringHelpers;
use LibreNMS\Util\Url;

/**
 * @extends TableController<EntPhysical>
 */
class InventoryController extends TableController
{
    protected ?string $model = EntPhysical::class;
    protected array $default_sort = ['device' => 'asc'];

    public function rules(): array
    {
        return [
            ...EntPhysical::filterValidationRules(),
            'device' => 'nullable|int',
            'descr' => 'nullable|string',
            'model' => 'nullable|string',
            'serial' => 'nullable|string',
            'sort' => 'sometimes|array',
        ];
    }

    protected function searchFields(Request $request): array
    {
        return ['entPhysicalDescr', 'entPhysicalModelName', 'entPhysicalSerialNum'];
    }

    protected function sortFields(Request $request): array
    {
        return [
            'device' => 'device_id',
            'name' => 'entPhysicalName',
            'descr' => 'entPhysicalDescr',
            'model' => 'entPhysicalModelName',
            'serial' => 'entPhysicalSerialNum',
            'type' => 'part_type',
        ];
    }

    protected function sort(Request $request, Builder $query): Builder
    {
        return parent::sort($request, $query)->orderBy('inventory_source')->orderBy('entPhysical_id');
    }

    protected function prepareExportQuery(Request $request): Builder
    {
        $this->validate($request, $this->rules());

        return parent::prepareExportQuery($request);
    }

    protected function baseQuery(Request $request): Builder
    {
        $this->authorize('viewAny', EntPhysical::class);

        $filters = $request->array('filter');
        $deviceFilters = Arr::only($filters, ['device_id']);
        $legacyDeviceId = $request->input('device') === null ? null : $request->integer('device');

        $query = EntPhysical::query()
            ->fromSub($this->inventoryRows($request->user(), $deviceFilters, $legacyDeviceId), 'entPhysical')
            ->with('device')
            ->applyFilters(Arr::except($filters, ['device_id']));

        // apply specific field filters
        $this->search($request->input('descr'), $query, ['entPhysicalDescr']);
        $this->search($request->input('model'), $query, ['entPhysicalModelName']);
        $this->search($request->input('serial'), $query, ['entPhysicalSerialNum']);

        return $query;
    }

    /**
     * Scope both sources before building match candidates so the windows only process the selected devices.
     *
     * @param  array<string, array<string, mixed>>  $deviceFilters
     */
    private function inventoryRows(User $user, array $deviceFilters, ?int $legacyDeviceId): QueryBuilder
    {
        $physical = EntPhysical::hasAccess($user)
            ->applyFilters($deviceFilters)
            ->when($legacyDeviceId !== null, fn (Builder $query) => $query->where('entPhysical.device_id', $legacyDeviceId))
            ->select([
                'entPhysical.entPhysical_id',
                'entPhysical.device_id',
                'entPhysical.entPhysicalDescr',
                'entPhysical.entPhysicalName',
                'entPhysical.entPhysicalModelName',
                'entPhysical.entPhysicalSerialNum',
            ])->selectRaw('0 as inventory_source');

        if (! Gate::forUser($user)->allows('viewAny', Transceiver::class)) {
            return $physical->selectRaw("'physical' as part_type")->toBase();
        }

        $transceivers = Transceiver::hasAccess($user)
            ->applyFilters($deviceFilters)
            ->when($legacyDeviceId !== null, fn (Builder $query) => $query->where('transceivers.device_id', $legacyDeviceId));
        $matches = $this->transceiverMatches($physical, $transceivers);

        $physical->leftJoinSub((clone $matches)->select('entPhysical_id'), 'matched', 'matched.entPhysical_id', '=', 'entPhysical.entPhysical_id')
            ->selectRaw("case when matched.entPhysical_id is null then 'physical' else 'transceiver' end as part_type");

        $transceivers->leftJoin('ports', function (JoinClause $join): void {
            $join->on('ports.port_id', '=', 'transceivers.port_id')->on('ports.device_id', '=', 'transceivers.device_id');
        })->whereNotIn('transceivers.id', $matches->select('transceiver_id'))
            ->select([
                'transceivers.id as entPhysical_id',
                'transceivers.device_id',
                'transceivers.type as entPhysicalDescr',
            ])
            ->selectRaw("coalesce(nullif(ports.ifName, ''), nullif(ports.ifDescr, ''), transceivers.`index`) as entPhysicalName")
            ->addSelect(['transceivers.model as entPhysicalModelName', 'transceivers.serial as entPhysicalSerialNum'])
            ->selectRaw('1 as inventory_source')
            ->selectRaw("'transceiver' as part_type");

        return $physical->toBase()->unionAll($transceivers->toBase());
    }

    /**
     * Some discoverers store an interface index in entity_physical_index.
     * Require matching identity fields and exactly one candidate on each side.
     *
     * @param  Builder<EntPhysical>  $physical
     * @param  Builder<Transceiver>  $transceivers
     */
    private function transceiverMatches(Builder $physical, Builder $transceivers): QueryBuilder
    {
        $candidates = (clone $transceivers)
            ->joinSub((clone $physical)->addSelect('entPhysical.entPhysicalIndex'), 'physical', function (JoinClause $join): void {
                $join->on('physical.device_id', '=', 'transceivers.device_id')
                    ->on('physical.entPhysicalIndex', '=', 'transceivers.entity_physical_index')
                    ->on('physical.entPhysicalModelName', '=', 'transceivers.model')
                    ->on('physical.entPhysicalSerialNum', '=', 'transceivers.serial');
            })
            ->whereRaw("trim(transceivers.model) <> '' and trim(transceivers.serial) <> ''")
            ->selectRaw('physical.entPhysical_id, transceivers.id as transceiver_id')
            ->selectRaw('count(*) over (partition by physical.entPhysical_id) as physical_matches')
            ->selectRaw('count(*) over (partition by transceivers.id) as transceiver_matches');

        return $candidates->getQuery()->newQuery()
            ->fromSub($candidates, 'candidates')
            ->where('physical_matches', 1)
            ->where('transceiver_matches', 1);
    }

    /**
     * @param  EntPhysical  $model
     * @return array<string, scalar>
     */
    public function formatItem(Model $model): array
    {
        return [
            'device' => Url::modernDeviceLink($model->device),
            'descr' => htmlspecialchars((string) $model->entPhysicalDescr),
            'name' => htmlspecialchars((string) $model->entPhysicalName),
            'model' => htmlspecialchars((string) $model->entPhysicalModelName),
            'serial' => htmlspecialchars((string) $model->entPhysicalSerialNum),
            'type' => __('inventory.' . $model->getAttribute('part_type')),
        ];
    }

    /**
     * Get headers for CSV export
     */
    protected function getExportHeaders(): array
    {
        return [
            'Device',
            'Description',
            'Name',
            'Model',
            'Serial Number',
            'Type',
        ];
    }

    /**
     * Format a row for CSV export
     *
     * @param  EntPhysical  $entPhysical
     */
    protected function formatExportRow(Model $entPhysical): array
    {
        return [
            ...array_map(StringHelpers::neutralizeCsvFormula(...), [
                $entPhysical->device->display ?? '',
                $entPhysical->entPhysicalDescr,
                $entPhysical->entPhysicalName,
                $entPhysical->entPhysicalModelName,
                $entPhysical->entPhysicalSerialNum,
            ]),
            __('inventory.' . $entPhysical->getAttribute('part_type')),
        ];
    }
}
