<?php
/**
 * AlertSchedule.php
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
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlertSchedule extends Model
{
    public $timestamps = false;
    protected $table = 'alert_schedule';
    protected $primaryKey = 'schedule_id';

    // ---- Define Relationships ----

    public function devices()
    {
        return $this->morphedByMany('App\Models\Device', 'alert_schedulable', 'alert_schedulables', 'schedule_id', 'schedule_id');
    }

    public function deviceGroups()
    {
        return $this->morphedByMany('App\Models\DeviceGroup', 'alert_schedulable');
    }
}
