<?php

/*
 * TnmsNeInfoController.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2023 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Table;

use App\Models\Device;
use App\Models\TnmsneInfo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * @extends TableController<TnmsneInfo>
 */
class TnmsneController extends TableController
{
    protected function rules(): array
    {
        return [
            'device_id' => 'nullable|integer',
        ];
    }

    protected function sortFields(Request $request): array
    {
        return [
            'neName',
            'neLocation',
            'neType',
            'neOpMode',
            'neAlarm',
            'neOpState',
        ];
    }

    protected function searchFields(Request $request): array
    {
        return [
            'neName',
            'neLocation',
            'neType',
            'neOpMode',
            'neAlarm',
            'neOpState',
        ];
    }

    protected function filterFields(Request $request): array
    {
        return ['device_id'];
    }

    /**
     * @inheritDoc
     */
    protected function baseQuery(Request $request): Builder
    {
        $this->authorize('viewAny', Device::class);

        return TnmsneInfo::hasAccess($request->user());
    }

    /**
     * @param  TnmsneInfo  $model
     * @return array<string, scalar>
     */
    public function formatItem(Model $model): array
    {
        $neOp = $model->neOpMode == 'operation'
            ? '<span style="min-width:40px;" class="label label-success">operation</span>'
            : '<span style="min-width:40px;" class="label label-danger">' . htmlspecialchars((string) $model->neOpMode) . '</span>';

        $opState = $model->neOpState == 'enabled'
            ? '<td class="list"><span style="min-width:40px;" class="label label-success">enabled</span></td>'
            : '<td class="list"><span style="min-width:40px;" class="label label-danger">' . htmlspecialchars((string) $model->neOpState) . '</span></td>';

        return [
            'neName' => htmlspecialchars((string) $model->neName),
            'neLocation' => htmlspecialchars((string) $model->neLocation),
            'neType' => htmlspecialchars((string) $model->neType),
            'neOpMode' => $neOp,
            'neAlarm' => $this->getAlarmLabel($model->neAlarm),
            'neOpState' => $opState,
        ];
    }

    private function getAlarmLabel(string $neAlarm): string
    {
        return match ($neAlarm) {
            'cleared' => '<span style="min-width:40px;" class="label label-success">cleared</span>',
            'warning' => '<span style="min-width:40px;" class="label label-warning">warning</span>',
            'minor', 'major', 'critical', 'indeterminate' => '<span style="min-width:40px;" class="label label-danger">' . htmlspecialchars($neAlarm) . '</span>',
            default => '<span style="min-width:40px;" class="label label-default">' . htmlspecialchars($neAlarm) . '</span>',
        };
    }
}
