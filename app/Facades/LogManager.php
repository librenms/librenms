<?php
/**
 * Log.php
 *
 * Extending the built in logging to add an event logger function
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Facades;

use Illuminate\Support\Facades\Auth;
use LibreNMS\Enum\Alert;

class LogManager extends \Illuminate\Log\LogManager
{
    /**
     * Log events to the event table
     *
     * @param string $text message describing the event
     * @param \App\Models\Device|int $device device array or device_id
     * @param string $type brief category for this event. Examples: sensor, state, stp, system, temperature, interface
     * @param int $severity 1: ok, 2: info, 3: notice, 4: warning, 5: critical, 0: unknown
     * @param int $reference the id of the referenced entity.  Supported types: interface
     */
    public function event($text, $device = null, $type = null, $severity = Alert::INFO, $reference = null)
    {
        (new \App\Models\Eventlog([
            'device_id' => $device instanceof \App\Models\Device ? $device->device_id : $device,
            'reference' => $reference,
            'type' => $type,
            'datetime' => \Carbon\Carbon::now(),
            'severity' => $severity,
            'message' => $text,
            'username' => Auth::user() ? Auth::user()->username : '',
        ]))->save();
    }
}
