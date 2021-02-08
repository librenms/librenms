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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Table;

use App\Models\AlertSchedule;
use Carbon\Carbon;
use DB;

class AlertScheduleController extends TableController
{
    protected $default_sort = ['title' => 'asc', 'start' => 'asc'];

    protected function baseQuery($request)
    {
        return AlertSchedule::query();
    }

    protected function searchFields($request)
    {
        return['title', 'start', 'end'];
    }

    protected function sortFields($request)
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
        ];
    }

    /**
     * @param AlertSchedule $schedule
     * @return array
     */
    public function formatItem($schedule)
    {
        return [
            'title' => $schedule->title,
            'notes' => $schedule->notes,
            'id' => $schedule->schedule_id,
            'start' => $schedule->recurring ? '' : $schedule->start->toDateTimeString('minutes'),
            'end' => $schedule->recurring ? '' : $schedule->end->toDateTimeString('minutes'),
            'start_recurring_dt' => $schedule->recurring ? $schedule->start_recurring_dt : '',
            'start_recurring_hr' => $schedule->recurring ? $schedule->start_recurring_hr : '',
            'end_recurring_dt' => $schedule->recurring ? $schedule->end_recurring_dt : '',
            'end_recurring_hr' => $schedule->recurring ? $schedule->end_recurring_hr : '',
            'recurring' => $schedule->recurring ? __('Yes') : __('No'),
            'recurring_day' => $schedule->recurring ? implode(',', $schedule->recurring_day) : '',
            'status' => $schedule->status,
        ];
    }
}
