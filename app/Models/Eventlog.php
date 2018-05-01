<?php
/**
 * Eventlog.php
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

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use LibreNMS\Authentication\Auth;

class Eventlog extends Model
{
    public $timestamps = false;
    protected $table = 'eventlog';
    protected $primaryKey = 'event_id';
    protected $fillable = ['message', 'reference', 'type', 'severity'];

    /**
     * Log events to the event table
     *
     * @param string $message message describing the event
     * @param Device|int $device device array or device_id
     * @param string $type brief category for this event. Examples: sensor, state, stp, system, temperature, interface
     * @param int $severity 1: ok, 2: info, 3: notice, 4: warning, 5: critical, 0: unknown
     * @param int $reference the id of the referenced entity.  Supported types: interface
     * @return bool
     */
    public static function event($message, $device = null, $type = null, $severity = 2, $reference = null)
    {
        $vars = get_defined_vars();
        $event = new static($vars);

        if (is_int($device)) {
            $event->device_id = $device;
        } elseif ($device instanceof Device) {
            $event->device_id = $device->device_id;
        } else {
            $event->device_id = 0;
        }
        $event->host = $event->device_id; // Legacy ??
        $event->datetime = Carbon::now();
        $event->username = Auth::user()->username ?: '';

        return $event->save();
    }

    // ---- Define Relationships ----

    public function device()
    {
        return $this->belongsTo('App\Models\Device', 'device_id');
    }
}
