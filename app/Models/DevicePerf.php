<?php
/**
 * DevicePerf.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Models;

class DevicePerf extends DeviceRelatedModel
{
    protected $table = 'device_perf';
    protected $fillable = ['device_id', 'timestamp', 'xmt', 'rcv', 'loss', 'min', 'max', 'avg'];
    protected $casts = [
        'xmt' => 'integer',
        'rcv' => 'integer',
        'loss' => 'integer',
        'min' => 'float',
        'max' => 'float',
        'avg' => 'float',
    ];
    public $timestamps = false;
    const CREATED_AT = 'timestamp';
    protected $attributes = [
        'min' => 0,
        'max' => 0,
        'avg' => 0,
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->timestamp = $model->freshTimestamp();
        });
    }
}
