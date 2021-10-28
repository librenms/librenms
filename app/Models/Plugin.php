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
    protected $casts = ['plugin_active' => 'bool', 'settings' => 'array'];

    // ---- Query scopes ----

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsActive($query)
    {
        return $query->where('plugin_active', 1);
    }

    public function scopeVersionOne($query)
    {
        return $query->where('version', 1);
    }

    public function scopeVersionTwo($query)
    {
        return $query->where('version', 2);
    }
}
