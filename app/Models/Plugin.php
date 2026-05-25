<?php

/**
 * Plugin.php
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

use Illuminate\Database\Eloquent\Builder;

class Plugin extends BaseModel
{
    public $timestamps = false;
    protected $primaryKey = 'plugin_id';
    protected $fillable = ['plugin_name', 'plugin_active', 'version', 'settings'];

    /**
     * @return array{plugin_active: 'bool', settings: 'array'}
     */
    protected function casts(): array
    {
        return [
            'plugin_active' => 'bool',
            'settings' => 'array',
        ];
    }

    // ---- Query scopes ----

    /**
     * Scope a query to only include active plugins.
     */
    public function scopeIsActive(Builder $query): Builder
    {
        return $query->where('plugin_active', 1);
    }

    /**
     * Scope a query to only include version 1 plugins.
     */
    public function scopeVersionOne(Builder $query): Builder
    {
        return $query->where('version', 1);
    }

    /**
     * Scope a query to only include version 2 plugins.
     */
    public function scopeVersionTwo(Builder $query): Builder
    {
        return $query->where('version', 2);
    }
}
