<?php
/**
 * app/Models/Alert.php
 *
 * Model for access to alerts table data
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
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    public $timestamps = false;

    // ---- Query scopes ----

    /**
     * Only select active alerts
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive($query)
    {
        return $query->where('state', '=', '1');
    }

    /**
     * Only select active alerts
     * @param Builder $query
     * @return Builder
     */
    public function scopeAcknowledged($query)
    {
        return $query->where('state', '=', '2');
    }

    // ---- Define Relationships ----

    public function device()
    {
        return $this->belongsTo('App\Models\Device', 'device_id');
    }

    public function rule()
    {
        return $this->belongsTo('App\Models\AlertRule', 'rule_id', 'id');
    }

    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'devices_perms', 'device_id', 'user_id');
    }
}
