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

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Date;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AlertSchedule extends Model
{
    public $timestamps = false;
    protected $table = 'alert_schedule';
    protected $primaryKey = 'schedule_id';
    protected $appends = ['start_recurring_dt', 'end_recurring_dt', 'start_recurring_hr', 'end_recurring_hr'];

    private $timezone;
    private $days = [
        'Mo' => 1,
        'Tu' => 2,
        'We' => 3,
        'Th' => 4,
        'Fr' => 5,
        'Sa' => 6,
        'Su' => 7,
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->timezone = config('app.timezone');
    }

    // ---- Accessors/Mutators ----

    public function getRecurringDayAttribute() {
        return explode(',', str_replace(array_values($this->days), array_keys($this->days), $this->attributes['recurring_day']));
    }

    public function setRecurringDayAttribute($days) {
        $days = is_array($days) ? $days : explode(',', $days);
        $new_days = [];

        foreach ($days as $day) {
            if (isset($this->days[$day])) {
                $new_days[] = $this->days[$day];
            }
        }

        $this->attributes['recurring_day'] = implode(',', $new_days);
    }

    public function getStartAttribute() {
        return Date::parse($this->attributes['start'], 'UTC')->tz($this->timezone);
    }

    public function setStartAttribute($start) {
       $this->attributes['start'] = $this->fromDateTime(Date::parse($start)->tz('UTC'));
    }

    public function getEndAttribute() {
        return Date::parse($this->attributes['end'], 'UTC')->tz($this->timezone);
    }

    public function setEndAttribute($end) {
        $this->attributes['end'] = $this->fromDateTime(Date::parse($end)->tz('UTC'));
    }

    public function getStartRecurringDtAttribute()
    {
        return $this->start->toDateString();
    }

    public function getStartRecurringHrAttribute() {
        return $this->start->toTimeString('minute');
    }

    public function getEndRecurringDtAttribute() {
        $end = $this->end;
        return $end->year == '9000' ? null : $end->toDateString();
    }

    public function getEndRecurringHrAttribute() {
        return $this->end->toTimeString('minute');
    }

    public function setStartRecurringDtAttribute($date) {
        $this->start = $this->start->setDateFrom(Date::parse($date, $this->timezone));
    }

    public function setStartRecurringHrAttribute($time) {
        $this->start = $this->start->setTimeFrom(Date::parse($time, $this->timezone));
    }

    public function setEndRecurringDtAttribute($date) {
        $this->end = $this->end->setDateFrom(Date::parse($date ?: '9000-09-09', $this->timezone));
    }

    public function setEndRecurringHrAttribute($time) {
        $this->end = $this->end->setTimeFrom(Date::parse($time, $this->timezone));
    }

    // ---- Query scopes ----

    public function scopeIsActive($query)
    {
        return $query->where(function ($query) {
            $now = CarbonImmutable::now('UTC');

            $query->where(function ($query) use ($now) {
                // Non recurring simply between start and end
                $query->where('recurring', 0)
                    ->where('start', '<=', $now)
                    ->where('end', '>=', $now);
            })->orWhere(function ($query) use ($now) {
                $query->where('recurring', 1)
                    // Check the time is after the start date and before the end date, or end date is not set
                    ->where('start', '<=', $now)
                    ->where('end', '>=', $now)
                    ->whereTime('start', '<=', $now->toTimeString())
                    ->whereTime('end', '>=', $now->toTimeString())
                    // Check we are on the correct day of the week
                    ->where(function ($query) use ($now) {
                            /** @var Builder $query */
                            $query->where('recurring_day', 'like', $now->format('%N%'))
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
