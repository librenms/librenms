<?php

/**
 * AlertScheduleController.php
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

namespace App\Http\Controllers\Table;

use App\Models\AlertSchedule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use LibreNMS\Enum\MaintenanceBehavior;

/**
 * @extends TableController<AlertSchedule>
 */
class AlertScheduleController extends TableController
{
    protected array $default_sort = ['title' => 'asc', 'start' => 'asc'];

    protected function baseQuery(Request $request): Builder|\Illuminate\Database\Query\Builder
    {
        $this->authorize('viewAny', AlertSchedule::class);

        return AlertSchedule::query();
    }

    protected function searchFields(Request $request): array
    {
        return['title', 'start', 'end'];
    }

    protected function sortFields(Request $request): array
    {
        return [
            'start_recurring_dt' => DB::raw('DATE(`start`)'),
            'start_recurring_ht' => DB::raw('TIME(`start`)'),
            'end_recurring_dt' => DB::raw('DATE(`end`)'),
            'end_recurring_ht' => DB::raw('TIME(`end`)'),
            'title' => 'title',
            'recurring' => 'recurring',
            'start' => 'start',
            'end' => 'end',
            'status' => DB::raw("end < '" . Carbon::now('UTC') . "'"), // only partition lapsed
            'behavior' => 'behavior',
        ];
    }

    /**
     * @param  AlertSchedule  $model
     * @return array<string, scalar>
     */
    public function formatItem(Model $model): array
    {
        $behavior = match ($model->behavior) {
            MaintenanceBehavior::SkipAlerts->value => __('alerting.maintenance.behavior.options.skip_alerts'),
            MaintenanceBehavior::MuteAlerts->value => __('alerting.maintenance.behavior.options.mute_alerts'),
            MaintenanceBehavior::RunAlerts->value => __('alerting.maintenance.behavior.options.run_alerts'),
            default => 'Error: Unknown behavior',
        };

        return [
            'title' => htmlentities((string) $model->title),
            'notes' => htmlentities((string) $model->notes),
            'behavior' => $behavior,
            'id' => $model->schedule_id,
            'start' => $model->recurring ? '' : $model->start->toDateTimeString('minutes'),
            'end' => $model->recurring ? '' : $model->end->toDateTimeString('minutes'),
            'start_recurring_dt' => $model->recurring ? $model->start_recurring_dt : '',
            'start_recurring_hr' => $model->recurring ? $model->start_recurring_hr : '',
            'end_recurring_dt' => $model->recurring ? $model->end_recurring_dt : '',
            'end_recurring_hr' => $model->recurring ? $model->end_recurring_hr : '',
            'recurring' => $model->recurring ? __('Yes') : __('No'),
            'recurring_day' => $model->recurring ? htmlentities(implode(',', $model->recurring_day)) : '',
            'status' => $model->status,
        ];
    }
}
