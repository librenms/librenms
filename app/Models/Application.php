<?php
/**
 * Applications.php
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

use Illuminate\Database\Eloquent\SoftDeletes;
use LibreNMS\Util\StringHelpers;

class Application extends DeviceRelatedModel
{
    use SoftDeletes;
    public $timestamps = false;
    protected $primaryKey = 'app_id';
    protected $fillable = ['device_id', 'app_type', 'app_instance', 'app_status', 'app_state', 'data', 'deleted_at', 'discovered'];
    protected $casts = [
        'data' => 'array',
    ];

    // ---- Helper Functions ----

    public function displayName()
    {
        return StringHelpers::niceCase($this->app_type);
    }

    public function getShowNameAttribute()
    {
        return $this->displayName();
    }

    // ---- Define Relationships ----

    public function metrics()
    {
        return $this->hasMany(ApplicationMetric::class, 'app_id', 'app_id');
    }
}
