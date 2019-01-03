<?php
/**
 * AlertSchedule.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AlertSchedule extends Model
{
    public $timestamps = false;
    protected $table = 'alert_schedule';
    protected $primaryKey = 'schedule_id';

    // ---- Query scopes ----

    public function scopeIsActive($query)
    {
        // TODO use Carbon?
        return $query->where(function ($query) {
            $query->where(function ($query) {
                // Non recurring simply between start and end
                $query->where('recurring', 0)
                    ->where('start', '<=', DB::raw('NOW()'))
                    ->where('end', '>=', DB::raw('NOW()'));
            })->orWhere(function ($query) {
                $query->where('recurring', 1)
                    // Check the time is after the start date and before the end date, or end date is not set
                    ->where(function ($query) {
                        $query->where('start_recurring_dt', '<=', DB::raw("date_format(NOW(), '%Y-%m-%d')"))
                            ->where(function ($query) {
                                $query->where('end_recurring_dt', '>=', DB::raw("date_format(NOW(), '%Y-%m-%d')"))
                                    ->orWhereNull('end_recurring_dt')
                                    ->orWhere('end_recurring_dt', '0000-00-00')
                                    ->orWhere('end_recurring_dt', '');
                            });
                    })
                    // Check the time is between the start and end hour/minutes/seconds
                    ->where('start_recurring_hr', '<=', DB::raw("date_format(NOW(), '%H:%i:%s')"))
                    ->where('end_recurring_hr', '>=', DB::raw("date_format(NOW(), '%H:%i:%s')"))
                    // Check we are on the correct day of the week
                    ->where(function ($query) {
                            /** @var Builder $query */
                            $query->where('recurring_day', 'like', DB::raw("CONCAT('%', date_format(NOW(), '%w'), '%')"))
                                ->orWhereNull('recurring_day')
                                ->orWhere('recurring_day', '');
                    });
            });
        });
    }

    // ---- Define Relationships ----

    public function devices()
    {
        return $this->morphedByMany('App\Models\Device', 'alert_schedulable', 'alert_schedulables', 'schedule_id', 'schedule_id');
    }

    public function deviceGroups()
    {
        return $this->morphedByMany('App\Models\DeviceGroup', 'alert_schedulable');
    }
}
