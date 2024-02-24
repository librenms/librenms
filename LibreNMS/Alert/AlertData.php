<?php
/**
 * Template.php
 *
 * Base Template class
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

namespace LibreNMS\Alert;

use App\Models\Device;
use LibreNMS\Config;
use LibreNMS\Enum\AlertState;
use LibreNMS\Util\Time;

class AlertData extends \Illuminate\Support\Collection
{
    public function __get($name)
    {
        if ($this->has($name)) {
            return $this->get($name);
        }

        return "$name is not a valid \$alert data name";
    }

    public static function testData(Device $device, array $faults = []): array
    {
        return [
            'hostname' => $device->hostname,
            'device_id' => $device->device_id,
            'sysDescr' => $device->sysDescr,
            'sysName' => $device->sysName,
            'sysContact' => $device->sysContact,
            'os' => $device->os,
            'type' => $device->type,
            'ip' => $device->ip,
            'display' => $device->displayName(),
            'version' => $device->version,
            'hardware' => $device->hardware,
            'features' => $device->features,
            'serial' => $device->serial,
            'status' => $device->status,
            'status_reason' => $device->status_reason,
            'location' => (string) $device->location,
            'description' => $device->purpose,
            'notes' => $device->notes,
            'uptime' => $device->uptime,
            'uptime_short' => Time::formatInterval($device->uptime, true),
            'uptime_long' => Time::formatInterval($device->uptime),
            'title' => 'Testing transport from ' . Config::get('project_name'),
            'elapsed' => '11s',
            'alerted' => 0,
            'alert_id' => '000',
            'alert_notes' => 'This is the note for the test alert',
            'proc' => 'This is the procedure for the test alert',
            'rule_id' => '000',
            'id' => '000',
            'faults' => $faults,
            'uid' => '000',
            'severity' => 'critical',
            'rule' => 'macros.device = 1',
            'name' => 'Test-Rule',
            'string' => '#1: test => string;',
            'timestamp' => date('Y-m-d H:i:s'),
            'contacts' => AlertUtil::getContacts([$device->toArray()]),
            'state' => AlertState::ACTIVE,
            'msg' => 'This is a test alert',
            'builder' => '{}',
        ];
    }
}
