<?php

/**
 * DeviceOutage.php
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
 * @copyright  2020 Thomas Berberich
 * @author     Thomas Berberich <sourcehhdoctor@gmail.com>
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeviceOutage extends DeviceRelatedModel
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = ['going_down', 'up_again'];

    public static function startOutage(Device $device, ?int $startedAt = null): DeviceOutage
    {
        $startedAt ??= time();

        // Check for existing ongoing outage
        static::where('device_id', $device->device_id)
            ->whereNull('up_again')
            ->update(['ended_at' => $startedAt]);

        return static::create([
            'device_id' => $device->device_id,
            'started_at' => $startedAt,
        ]);
    }


    public function scopeOngoing($query)
    {
        return $query->whereNull('up_again');
    }

    public function scopeResolved($query)
    {
        return $query->whereNotNull('up_again');
    }
}
