<?php
/**
 * PollerGroup.php
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
 * @copyright  2020 Thomas Berberich
 * @author     Thomas Berberich <sourcehhdoctor@gmail.com>
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PollerGroup extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'id';
    protected $fillable = ['group_name', 'descr'];

    /**
     * Initialize this class
     */
    public static function boot()
    {
        parent::boot();

        static::deleting(function (PollerGroup $pollergroup) {
            // handle device poller group fallback to default poller
            $default_poller_id = \LibreNMS\Config::get('default_poller_group');
            $pollergroup->devices()->update(['poller_group' => $default_poller_id]);
        });
    }

    public static function list()
    {
        return self::query()->pluck('group_name', 'id')->prepend(__('General'), 0);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(\App\Models\Device::class, 'poller_group', 'id');
    }
}
