<?php

/**
 * MuninPluginDs.php
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
 * @link       https://www.librenms.org
 *
 * @copyright  2026 LibreNMS
 * @author     LibreNMS
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MuninPluginDs extends BaseModel
{
    protected $primaryKey = null;
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'mplug_id',
        'ds_name',
        'ds_type',
        'ds_label',
        'ds_cdef',
        'ds_draw',
        'ds_graph',
        'ds_info',
        'ds_extinfo',
        'ds_max',
        'ds_min',
        'ds_negative',
        'ds_warning',
        'ds_critical',
        'ds_colour',
        'ds_sum',
        'ds_stack',
        'ds_line',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\MuninPlugin, $this>
     */
    public function muninPlugin(): BelongsTo
    {
        return $this->belongsTo(MuninPlugin::class, 'mplug_id', 'mplug_id');
    }
}
