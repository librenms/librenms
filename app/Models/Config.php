<?php
/**
 * Config.php
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

class Config extends BaseModel
{
    public $timestamps = false;
    protected $table = 'config';
    public $primaryKey = 'config_id';
    protected $fillable = [
        'config_name',
        'config_value',
    ];
    protected $casts = [
        'config_default' => 'array',
    ];

    // ---- Accessors/Mutators ----

    public function getConfigValueAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setConfigValueAttribute($value)
    {
        $this->attributes['config_value'] = json_encode($value, JSON_UNESCAPED_SLASHES);
    }

    // ---- Query Scopes ----

    public function scopeWithChildren($query, $name)
    {
        return $query->where('config_name', $name)
            ->orWhere('config_name', 'like', "$name.%");
    }
}
