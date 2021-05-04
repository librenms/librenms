<?php
/**
 * Component.php
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

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Component extends DeviceRelatedModel
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'component';
    protected $fillable = ['device_id', 'type', 'label', 'status', 'disabled', 'ignore', 'error'];

    // ---- Accessors/Mutators ----

    public function setStatusAttribute($status)
    {
        $this->attributes['status'] = (int) $status;
    }

    public function setDisabledAttribute($disabled)
    {
        $this->attributes['disabled'] = (int) $disabled;
    }

    public function setIgnoreAttribute($ignore)
    {
        $this->attributes['ignore'] = (int) $ignore;
    }

    // ---- Define Relationships ----

    public function logs(): HasMany
    {
        return $this->hasMany(\App\Models\ComponentStatusLog::class, 'component_id', 'id');
    }

    public function prefs(): HasMany
    {
        return $this->hasMany(\App\Models\ComponentPref::class, 'component', 'id');
    }
}
