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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Date;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;
use LibreNMS\Enum\AlertScheduleStatus;
use LibreNMS\Enum\MaintenanceBehavior;

/**
 * @method static \Database\Factories\AlertScheduleFactory factory(...$parameters)
 */
class AlertSchedule extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'alert_schedule';
    protected $primaryKey = 'schedule_id';
    protected $appends = ['start_recurring_dt', 'end_recurring_dt', 'start_recurring_hr', 'end_recurring_hr', 'status'];
    protected $fillable = ['title', 'notes', 'recurring', 'behavior', 'timezone'];

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
        $this->attributes['timezone'] ??= config('app.timezone');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'behavior' => MaintenanceBehavior::class,
        ];
    }

    // ---- Accessors/Mutators ----

    public function getRecurringDayAttribute()
    {
        if (empty($this->attributes['recurring_day'])) {
            return [];
        }

        return explode(',', str_replace(array_values($this->days), array_keys($this->days), $this->attributes['recurring_day']));
    }

    public function setRecurringDayAttribute($days)
    {
        $this->attributes['recurring_day'] = is_array($days) ? implode(',', $days) : $days;
    }

    public function getStartAttribute()
    {
        return Date::parse($this->attributes['start'], 'UTC')->tz($this->timezone);
    }

    public function setStartAttribute($start)
    {
        $this->attributes['start'] = $this->fromDateTime(Date::parse($start, $this->timezone)->tz('UTC'));
    }

    public function getEndAttribute()
    {
        return Date::parse($this->attributes['end'], 'UTC')->tz($this->timezone);
    }

    public function setEndAttribute($end)
    {
        $this->attributes['end'] = $this->fromDateTime(Date::parse($end, $this->timezone)->tz('UTC'));
    }

    public function getStartRecurringDtAttribute()
    {
        return $this->start->toDateString();
    }

    public function getStartRecurringHrAttribute()
    {
        return $this->start->toTimeString('minute');
    }

    public function getEndRecurringDtAttribute()
    {
        $end = $this->end;

        return $end->year == '9000' ? null : $end->toDateString();
    }

    public function getEndRecurringHrAttribute()
    {
        return $this->end->toTimeString('minute');
    }

    public function setStartRecurringDtAttribute($date)
    {
        $this->start = $this->start->setDateFrom(Date::parse($date, $this->timezone));
    }

    public function setStartRecurringHrAttribute($time)
    {
        $this->start = $this->start->setTimeFrom(Date::parse($time, $this->timezone));
    }

    public function setEndRecurringDtAttribute($date)
    {
        $this->end = $this->end->setDateFrom(Date::parse($date ?: '9000-09-09', $this->timezone));
    }

    public function setEndRecurringHrAttribute($time)
    {
        $this->end = $this->end->setTimeFrom(Date::parse($time, $this->timezone));
    }

    /**
     * @return int \LibreNMS\Enum\AlertScheduleStatus
     */
    public function getStatusAttribute()
    {
        $now = Carbon::now($this->timezone);

        if ($now > $this->end) {
            return AlertScheduleStatus::LAPSED;
        }

        if (! $this->recurring) {
            return $now > $this->start ? AlertScheduleStatus::ACTIVE : AlertScheduleStatus::SET;
        }

        // recurring
        // compare wall clock times in the schedule timezone so the window doesn't shift with DST
        $now_time = $now->format('H:i:s');
        $start_time = $this->start->format('H:i:s');
        $end_time = $this->end->format('H:i:s');
        $after_start = $now > $this->start;
        $spans_days = $start_time > $end_time;

        // check inside start and end times or outside start and end times (if we span a day)
        $active = $spans_days ? ($after_start && ($now_time < $end_time || $now_time >= $start_time)) : ($now_time >= $start_time && $now_time < $end_time);

        $on_day = empty($this->attributes['recurring_day']) || Str::contains($this->attributes['recurring_day'], $now->format('N'));

        return $active && $on_day ? AlertScheduleStatus::ACTIVE : AlertScheduleStatus::SET;
    }

    // ---- Query scopes ----

    public function scopeIsActive($query)
    {
        $now = CarbonImmutable::now('UTC');

        // recurring windows depend on each schedule's timezone, which SQL can't evaluate without the server's timezone tables
        $activeRecurringIds = self::query()
            ->where('recurring', 1)
            ->where('start', '<=', $now)
            ->where('end', '>=', $now)
            ->get(['schedule_id', 'recurring', 'start', 'end', 'recurring_day', 'timezone'])
            ->filter(fn (self $schedule) => $schedule->status === AlertScheduleStatus::ACTIVE)
            ->modelKeys();

        return $query->where(function ($query) use ($now, $activeRecurringIds): void {
            $query->where(function ($query) use ($now): void {
                $query->where('recurring', 0)
                    ->where('start', '<=', $now)
                    ->where('end', '>=', $now);
            })->orWhereIn($query->qualifyColumn('schedule_id'), $activeRecurringIds);
        });
    }

    // ---- Define Relationships ----
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<\App\Models\Device, $this>
     */
    public function devices(): MorphToMany
    {
        return $this->morphedByMany(Device::class, 'alert_schedulable', 'alert_schedulables', 'schedule_id', 'alert_schedulable_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<\App\Models\DeviceGroup, $this>
     */
    public function deviceGroups(): MorphToMany
    {
        return $this->morphedByMany(DeviceGroup::class, 'alert_schedulable', 'alert_schedulables', 'schedule_id', 'alert_schedulable_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<\App\Models\Location, $this>
     */
    public function locations(): MorphToMany
    {
        return $this->morphedByMany(Location::class, 'alert_schedulable', 'alert_schedulables', 'schedule_id', 'alert_schedulable_id');
    }

    public function __toString(): string
    {
        return ($this->recurring ?
            'Recurring Alert Schedule (' . implode(',', $this->recurring_day) . ') ' :
            'Alert Schedule ')
            . "start: $this->start end: $this->end";
    }
}
