<?php
/**
 * PortGroup.php
 *
 * Groups of ports
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
 * @link       http://librenms.org
 * @copyright  2020 Thomas Berberich
 * @author     Thomas Berberich <sourcehhdoctor@gmail.com>
 */

namespace App\Models;
use Permissions;

class PortGroup extends BaseModel
{
    public $timestamps = false;
    protected $fillable = ['name', 'desc'];

#    TODO: bei delete -> fall back to default !
    public static function boot()
    {
        parent::boot();
#        static::deleting(function (PortGroup $portGroup) {
#            $portGroup->ports()->detach();
#        });

#
#        static::deleting(function (DeviceGroup $deviceGroup) {
#            $deviceGroup->devices()->detach();
#        });
#
#        static::saving(function (DeviceGroup $deviceGroup) {
#            if ($deviceGroup->isDirty('rules')) {
#                $deviceGroup->rules = $deviceGroup->getParser()->generateJoins()->toArray();
#            }
#        });
#
#        static::saved(function (DeviceGroup $deviceGroup) {
#            if ($deviceGroup->isDirty('rules')) {
#                $deviceGroup->updateDevices();
#            }
#        });
    }

    // ---- Query Scopes ----

    public function scopeHasAccess($query, User $user)
    {
        if ($user->hasGlobalRead()) {
            return $query;
        }

        return $query->whereIn('id', Permissions::portGroupsForUser($user));
    }

    public function ports()
    {
        return $this->belongsto(\App\Models\Port::class, 'port_group', 'id');
    }

}
